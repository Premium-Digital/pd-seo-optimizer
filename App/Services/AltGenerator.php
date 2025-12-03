<?php
namespace PdSeoOptimizer\Services;
use PdSeoOptimizer\Logger;
use PdSeoOptimizer\Services\OpenAiClient;

class AltGenerator
{
    private $openAi;

    public function __construct(OpenAiClient $openAi)
    {
        $this->openAi = $openAi;
    }

    public function generateForPosts(array $postIds): array
    {
        $imagesProcessed = 0;

        foreach ($postIds as $postId) {
            $post = get_post($postId);

            if ($post->post_type === 'attachment' && strpos($post->post_mime_type, 'image/') === 0) {
                // Skip direct attachments - they should use generateForAttachments()
                continue;
            }

            $attachments = $this->getImagesFromPost($postId);

            foreach ($attachments as $attachmentId) {
                if (empty(get_post_meta($attachmentId, '_wp_attachment_image_alt', true))) {
                    $alt = $this->generateAlt($postId, $attachmentId);
                    if ($alt) {
                        update_post_meta($attachmentId, '_wp_attachment_image_alt', $alt);
                        $imagesProcessed++;
                    }
                }
            }
        }

        return ['images_count' => $imagesProcessed];
    }

    /**
     * Generate alt text for attachment IDs directly from media library
     *
     * @param array $attachmentIds Array of attachment post IDs
     * @return array Array with images_count and optional alt_text for single attachment
     */
    public function generateForAttachments(array $attachmentIds): array
    {
        $imagesProcessed = 0;
        $generatedAlt = null;

        foreach ($attachmentIds as $attachmentId) {
            $attachment = get_post($attachmentId);
            
            // Verify it's an image attachment
            if (!$attachment || $attachment->post_type !== 'attachment' || strpos($attachment->post_mime_type, 'image/') !== 0) {
                continue;
            }

            // Skip if alt text already exists
            if (!empty(get_post_meta($attachmentId, '_wp_attachment_image_alt', true))) {
                continue;
            }

            $alt = $this->generateAltForAttachment($attachmentId);
            if ($alt) {
                update_post_meta($attachmentId, '_wp_attachment_image_alt', $alt);
                $imagesProcessed++;
                // Store the alt text for single attachment cases
                $generatedAlt = $alt;
            }
        }

        $result = ['images_count' => $imagesProcessed];
        // Only include alt_text if processing a single attachment
        if (count($attachmentIds) === 1 && $generatedAlt) {
            $result['alt_text'] = $generatedAlt;
        }
        
        return $result;
    }


    private function getImagesFromPost(int $postId): array
    {
        $ids = [];

        $thumbnailId = get_post_thumbnail_id($postId);
        if ($thumbnailId) $ids[] = $thumbnailId;

        $attachments = get_attached_media('image', $postId);
        foreach ($attachments as $att) $ids[] = $att->ID;

        $post = get_post($postId);
           if ($post) {
               preg_match_all('/wp-image-(\d+)/', $post->post_content, $matches);
           if (!empty($matches[1])) {
               $ids = array_merge($ids, $matches[1]);
           }
    
           $galleryImages = get_post_gallery_images($postId);

            if(function_exists('wc_get_product') && is_object(\wc_get_product($postId))){
                $product = \wc_get_product($postId);
                $attachmentIds = $product->get_gallery_image_ids();
                $ids = array_merge($ids, $attachmentIds);
            }

           if (!empty($galleryImages)) {
               foreach ($galleryImages as $url) {
                   $id = attachment_url_to_postid($url);
                   if ($id) {
                       $ids[] = $id;
                   }
               }
           }
        }

        $ids = array_merge($ids, $this->getImagesFromElementorPost($postId));

        return array_unique($ids);
    }

    private function getImagesFromElementorPost(int $postId): array
    {
        $ids = [];
        $data = get_post_meta($postId, '_elementor_data', true);

        if (empty($data)) {
            return [];
        }

        $elements = json_decode($data, true);
        if (!is_array($elements)) {
            return [];
        }

        $extractIds = function ($element) use (&$extractIds, &$ids) {
            if (!is_array($element)) {
                return;
            }

            if (isset($element['settings'])) {
                $settings = $element['settings'];

                foreach (['gallery', 'carousel', 'slides', 'images', 'gallery_images', 'media_gallery', 'slides_images', 'carousel_items'] as $key) {
                    if (!empty($settings[$key]) && is_array($settings[$key])) {
                        foreach ($settings[$key] as $img) {
                            if (!empty($img['id'])) {
                                $ids[] = (int) $img['id'];
                            }
                        }
                    }
                }

                if (!empty($settings['_gallery']) && is_string($settings['_gallery'])) {
                    $decoded = json_decode($settings['_gallery'], true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $img) {
                            if (!empty($img['id'])) {
                                $ids[] = (int) $img['id'];
                            }
                        }
                    }
                }
            }

            foreach (['elements', 'children'] as $key) {
                if (!empty($element[$key]) && is_array($element[$key])) {
                    foreach ($element[$key] as $child) {
                        $extractIds($child);
                    }
                }
            }
        };

        foreach ($elements as $el) {
            $extractIds($el);
        }

        return array_unique($ids);
    }

    private function generateAlt(int $postId, int $attachmentId): ?string
    {
        $post = get_post($postId);
        $filePath = get_attached_file($attachmentId);
        $imageUrl = wp_get_attachment_url($attachmentId);

        $ngrokUrl = getenv('NGROK_URL');
        if ($ngrokUrl) {
            $imageUrl = str_replace("localhost", $ngrokUrl, $imageUrl);
        }

        // Validate image format before sending to OpenAI
        if (!$this->isSupportedImage($filePath)) {
            return null;
        }

        return $this->openAi->generateAltFromImage($post->post_title, $imageUrl, $filePath);
    }

    private function generateAltForAttachment(int $attachmentId): ?string
    {
        $attachment = get_post($attachmentId);
        $filePath = get_attached_file($attachmentId);
        $imageUrl = wp_get_attachment_url($attachmentId);

        $ngrokUrl = getenv('NGROK_URL');
        if ($ngrokUrl) {
            $imageUrl = str_replace("localhost", $ngrokUrl, $imageUrl);
        }

        // Validate image format before sending to OpenAI
        if (!$this->isSupportedImage($filePath)) {
              return null;
        }

        return $this->openAi->generateAltFromImage($attachment->post_title, $imageUrl, $filePath);
    }

    /**
     * Check whether an image file is in a supported format.
     * Preferred: check MIME via getimagesize, fallback to extension.
     * Supported formats: png, jpeg, gif, webp
     *
     * @param string|null $path Local filesystem path to the image
     * @return bool
     */
    private function isSupportedImage(?string $path): bool
    {
        if (empty($path) || !file_exists($path)) {
            return false;
        }

        $info = @getimagesize($path);
        if ($info && isset($info['mime'])) {
            $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
            return in_array(strtolower($info['mime']), $allowed, true);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true);
    }
}

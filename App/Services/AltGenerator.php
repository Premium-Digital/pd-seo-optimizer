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
                $attachments = [$postId];
            }else{
                $attachments = $this->getImagesFromPost($postId);
            }

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

            if(is_object(wc_get_product($postId))){
                $product = wc_get_product($postId);
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

        $imageUrl = str_replace("localhost", "9213eac6aafe.ngrok-free.app", $imageUrl);

        return $this->openAi->generateAltFromImage($post->post_title, $imageUrl, $filePath);
    }
}

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
            if (!empty($matches[1])) $ids = array_merge($ids, $matches[1]);
        }

        $ids = array_merge($ids, $this->getImagesFromElementorPost($postId));

        return array_unique($ids);
    }


    private function getImagesFromElementorPost(int $postId): array
    {
        $ids = [];
        $elementorData = get_post_meta($postId, '_elementor_data', true);
        if (!$elementorData) {
            return $ids;
        }

        $data = json_decode($elementorData, true);
        if (!$data) {
            return $ids;
        }

        $extractImages = function ($elements) use (&$ids, &$extractImages) {
            foreach ($elements as $el) {
                if (isset($el['widgetType']) && $el['widgetType'] === 'image') {
                    if (!empty($el['settings']['image']['id'])) {
                        $ids[] = (int) $el['settings']['image']['id'];
                    }
                }

                if (!empty($el['elements'])) {
                    $extractImages($el['elements']);
                }
            }
        };

        $extractImages($data);

        return array_unique($ids);
    }

    private function generateAlt(int $postId, int $attachmentId): ?string
    {
        $post = get_post($postId);
        $filePath = get_attached_file($attachmentId);
        $imageUrl = wp_get_attachment_url($attachmentId);

        return $this->openAi->generateAltFromImage($post->post_title, $imageUrl, $filePath);
    }
}

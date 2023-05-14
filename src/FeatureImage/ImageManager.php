<?php


namespace NovaThinKit\FeatureImage;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ImageManager
{
    /**
     * Store uploaded file
     *
     * @param UploadedFile $image
     * @param array $options
     *
     * @return string
     * @throws FeatureImageException|FeatureImageUploadException
     */
    public function storeUploaded(UploadedFile $image, array $options = []): string;

    /**
     * Store uploaded file
     *
     * @param string|null $filename
     *
     * @return bool
     * @throws FeatureImageException
     */
    public function delete(?string $filename = null): bool;

    /**
     * Check is file exists
     *
     * @param string|null $format
     * @param string|null $filename
     *
     * @return bool
     * @throws FeatureImageException
     */
    public function exists(?string $format = null, ?string $filename = null): bool;

    /**
     * Get full path to file
     *
     * @param string|null $format
     * @param string|null $filename
     *
     * @return string|null
     * @throws FeatureImageException
     */
    public function path(?string $format = null, ?string $filename = null): ?string;

    /**
     * Get file url
     *
     * @param string|null $format
     * @param string|null $filename
     *
     * @return string|null
     * @throws FeatureImageException
     */
    public function url(?string $format = null, ?string $filename = null): ?string;

    /**
     * Download streamed response
     *
     * @param string|null $format
     * @param string|null $filename
     * @param string|null $name
     * @param array $headers
     * @return StreamedResponse|null
     */
    public function download(?string $format = null, ?string $filename = null, ?string $name = null, array $headers = []): ?StreamedResponse;
}

<?php


namespace NovaThinKit\FeatureImage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NovaThinKit\FeatureImage\Models\WithFeatureImage;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeatureImageManager implements ImageManager
{
    /**
     * Files storage disk
     * @var string|null
     */
    public ?string $disk;

    /**
     * Predefined formats
     * @var array
     */
    public array $formats;

    /**
     * Deleted formats
     * @var array
     */
    public array $deletedFormats = [];

    /**
     * Generate default responsive formats
     * @var bool
     */
    public bool $responsive;

    /**
     * Filename
     * @var WithFeatureImage|null
     */
    public ?WithFeatureImage $model = null;

    /**
     * Fallback to display specific image if not uploaded.
     *
     * @var string|null
     */
    protected ?string $defaultPath = null;

    protected ?string $tag = null;

    public function __construct(?string $disc = null, array $formats = [], bool $responsive = false, array $options = [])
    {
        $this->disk       = $disc;
        $this->formats    = $formats;
        $this->responsive = $responsive;

        /** @deprecated */
        if (isset($options['column'])) {
            $this->tag = $options['column'];
        }

        if (array_key_exists('default', $options)) {
            $this->defaultPath = $options['default'];
        }

        if (isset($options['deletedFormats']) && is_array($options['deletedFormats'])) {
            $this->deletedFormats = $options['deletedFormats'];
        }
    }

    public static function fromConfig(array $config): static
    {
        return new static(
            $config['disk']    ?? null,
            $config['formats'] ?? [],
            (bool)($config['responsive'] ?? false),
            // TODO: $config['options'] should not be present on config anymore
            array_merge($config['options'] ?? [], Arr::except($config, [
                'disk',
                'formats',
                'responsive',
            ])),
        );
    }

    public function tag(): ?string
    {
        return $this->tag;
    }

    public function withDefaultPath(?string $defaultPath): static
    {
        $this->defaultPath = $defaultPath;

        return $this;
    }

    public function withoutDefaultPath(): static
    {
        $this->defaultPath = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function storeUploaded(UploadedFile $image, array $options = []): string
    {
        if ($this->filename()) {
            $this->delete();
        }

        $storage     = $this->storage();
        $fileDir     = $this->directory();
        $newFileName = Str::random(30);
        $newFileExt  = '.' . $image->extension();

        $storage->makeDirectory($fileDir);

        $storagePath = "{$fileDir}{$newFileName}{$newFileExt}";
        $path        = $storage->path($storagePath);

        Image::load($image->path())
            ->fit(Manipulations::FIT_MAX, 2800, 1800)
            ->optimize()
            ->save($path);

        foreach ($this->formats as $format => $configuration) {
            $formatPath = $storage->path("{$fileDir}{$newFileName}-{$format}{$newFileExt}");
            $builder    = Image::load($image->path());
            if (!empty($configuration['methods']) && is_array($configuration['methods'])) {
                foreach ($configuration['methods'] as $method => $attrs) {
                    call_user_func_array([$builder, $method], $attrs);
                }
            }
            $builder->save($formatPath);
        }

        if ($this->responsive) {
            $width = 1600;
            while ($width >= 400) {
                $scalePath = $storage->path("{$fileDir}{$newFileName}-{$width}{$newFileExt}");
                Image::load($path)
                    ->width($width)
                    ->optimize()
                    ->save($scalePath);
                $width = $width - 400;
            }
        }


        return $storagePath;
    }

    /**
     * @inheritDoc
     */
    public function delete(?string $filename = null): bool
    {
        if ($filename) {
            return $this->storage()->delete($filename);
        }
        $fileName = $this->filename();

        if (!$fileName) {
            return false;
        }

        $filesToDelete = [
            $fileName,
        ];

        [$name, $extension] = $this->explodeFilename($fileName);

        foreach (array_keys($this->formats) as $format) {
            $filesToDelete[] = "{$name}-{$format}.{$extension}";
        }

        foreach ($this->deletedFormats as $format) {
            $filesToDelete[] = "{$name}-{$format}.{$extension}";
        }

        foreach ($this->storage()->files($this->directory()) as $file) {
            if (
                ($suffix = Str::between($file, "{$name}-", ".{$extension}")) &&
                is_numeric($suffix)
            ) {
                $filesToDelete[] = $file;
            }
        }

        return $this->storage()->delete(array_unique($filesToDelete));
    }

    /**
     * @inheritDoc
     */
    public function exists(?string $format = null, ?string $filename = null): bool
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        $path = $filename ?? $this->filename($format);
        if (!$path) {
            return false;
        }

        return $this->storage()->exists($path);
    }

    /**
     * @inheritDoc
     */
    public function path(?string $format = null, ?string $filename = null): ?string
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        $filename = $filename ?? ($this->filename($format) ?? $this->defaultPath);

        return $filename ? $this->storage()->path($filename) : null;
    }

    /**
     * @inheritDoc
     */
    public function url(?string $format = null, ?string $filename = null): ?string
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        $filename = $filename ?? ($this->filename($format) ?? $this->defaultPath);

        if (!$filename) {
            return null;
        }

        return $this->storage()->url($filename);
    }

    /**
     * @inheritDoc
     */
    public function download(?string $format = null, ?string $filename = null, ?string $name = null, array $headers = []): ?StreamedResponse
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        $filename = $filename ?? ($this->filename($format) ?? $this->defaultPath);

        if (!$filename) {
            return null;
        }

        return $this->storage()->download($filename, $name, $headers);
    }

    public function setTag(?string $tag = null): ImageManager
    {
        $this->tag = $tag;

        return $this;
    }

    public function setModel(WithFeatureImage $model): ImageManager
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): ?WithFeatureImage
    {
        return $this->model;
    }

    /**
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public function storage(): mixed
    {
        return Storage::disk($this->disk);
    }

    public function featureImageKey(): string
    {
        return ($this->model && method_exists($this->model, 'featureImageKey')) ? $this->model->featureImageKey($this->tag) : 'image';
    }

    public function filename(?string $format = null): ?string
    {
        $filename = $this->model?->{$this->featureImageKey()};

        if ($filename && $format) {
            [$name, $extension] = $this->explodeFilename($filename);
            $filename           = $name . "-{$format}." . $extension;
        }

        return $filename;
    }

    public function directory(): string
    {
        if ($this->model && method_exists($this->model, 'featureImageManagerDirectory')) {
            $directory = $this->model->featureImageManagerDirectory($this->tag);
        } elseif ($this->model && ($this->model instanceof Model)) {
            $directory = base64_encode(Str::slug($this->model->getMorphClass()) . '-' . $this->model->getKey());
        } else {
            $directory = '';
        }

        return rtrim($directory, '/') . '/';
    }

    protected function explodeFilename(string $fileName): array
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $name = Str::beforeLast($fileName, ".{$extension}");

        return [$name, $extension];
    }
}

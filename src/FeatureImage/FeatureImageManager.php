<?php


namespace NovaThinKit\FeatureImage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeatureImageManager implements ImageManager
{
    /**
     * Files storage disk
     * @var string
     */
    public string $disk;

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
     * @var Model|null
     */
    public ?Model $model = null;

    protected ?string $column = null;

    /**
     * Fallback to display specific image if not uploaded.
     *
     * @var string|null
     */
    protected ?string $defaultPath = null;

    public function __construct(string $disc, array $formats = [], bool $responsive = false, array $options = [])
    {
        $this->disk       = $disc;
        $this->formats    = $formats;
        $this->responsive = $responsive;

        if (isset($options['column'])) {
            $this->column = $options['column'];
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
            $config['disk'],
            $config['formats']    ?? [],
            $config['responsive'] ?? false,
            $config['options']    ?? [],
        );
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

    public function column(?string $column): static
    {
        $this->column = $column;

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
            if(
                ($suffix = Str::between($file, "{$name}-", ".{$extension}")) &&
                is_numeric($suffix)
            ) {
                $filesToDelete[] = $file;
            }
        }

        $isDeleted =  $this->storage()->delete(array_unique($filesToDelete));

        if(empty($this->storage()->files($this->directory()))) {
            $this->storage()->deleteDirectory($this->directory());
        }

        return $isDeleted;
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
        if(!$path) {
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
        if(!$filename) {
            return null;
        }

        return $this->storage()->path($filename);
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

        if(!$filename) {
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

        if(!$filename) {
            return null;
        }

        return $this->storage()->download($filename, $name, $headers);
    }

    public function setModel(Model $model): ImageManager
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): ?string
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

    protected function featureImageKey(): string
    {
        if (!$this->model) {
            throw new FeatureImageException('Model not set');
        }

        return method_exists($this->model, 'featureImageKey') ? $this->model->featureImageKey($this->column) : ($this->column ?? 'image');
    }

    protected function filename(?string $format = null): ?string
    {
        if (!$this->model) {
            throw new FeatureImageException('Model not set');
        }

        $filename = $this->model->{$this->featureImageKey()};

        if ($filename && $format) {
            [$name, $extension] = $this->explodeFilename($filename);
            $filename           = $name . "-{$format}." . $extension;
        }

        return $filename;
    }

    protected function directory(): string
    {
        if (!$this->model) {
            throw new FeatureImageException('Model not set');
        }


        if (method_exists($this->model, 'featureImageManagerDirectory')) {
            $directory = $this->model->featureImageManagerDirectory();
        } else {
            $directory = base64_encode(Str::slug($this->model->getMorphClass()) . '-' . $this->model->getKey());
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

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

    public function __construct(string $disc, array $formats = [], bool $responsive = false, array $options = [])
    {
        $this->disk       = $disc;
        $this->formats    = $formats;
        $this->responsive = $responsive;

        if (isset($options['column'])) {
            $this->column = $options['column'];
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

        return $this->storage()->deleteDirectory($this->directory());
    }

    /**
     * @inheritDoc
     */
    public function path(?string $format = null, ?string $filename = null): ?string
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        return $this->storage()->path($filename ?? $this->filename($format));
    }

    /**
     * @inheritDoc
     */
    public function url(?string $format = null, ?string $filename = null): ?string
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        return $this->storage()->url($filename ?? ($this->filename($format) ?? '_default.svg'));
    }

    /**
     * @inheritDoc
     */
    public function download(?string $format = null, ?string $filename = null, ?string $name = null, array $headers = []): StreamedResponse
    {
        if ($format && !in_array($format, array_keys($this->formats))) {
            $format = null;
        }

        return $this->storage()->download(($filename ?? ($this->filename($format) ?? '_default.svg')), $name, $headers);
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
    public function storage()
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
            $filename = Str::beforeLast($filename, '.') . "-{$format}." . Str::afterLast($filename, '.');
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
}

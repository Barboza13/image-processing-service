<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;

class ImageProcessorService
{
    protected ImageManager $manager;

    public function __construct(ImageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Cambiar tamaño de imagen manteniendo proporción
     */
    public function resize(string $imagePath, int $width, int $height, bool $maintainAspect = true): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        if ($maintainAspect) {
            // scale() mantiene la proporción automáticamente
            $image->scale($width, $height);
        } else {
            // resize() cambia a dimensiones exactas
            $image->resize($width, $height);
        }

        return $image;
    }

    /**
     * Recortar imagen (crop)
     */
    public function crop(string $imagePath, int $width, int $height, ?int $x = null, ?int $y = null): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        if ($x !== null && $y !== null) {
            // Crop desde coordenadas específicas
            $image->crop($width, $height, $x, $y);
        } else {
            // cover() hace crop inteligente manteniendo el centro
            $image->cover($width, $height);
        }

        return $image;
    }

    /**
     * Rotar imagen
     */
    public function rotate(string $imagePath, float $angle, string $backgroundColor = 'ffffff'): ImageInterface
    {
        $image = $this->manager->read($imagePath);
        $image->rotate($angle, $backgroundColor);

        return $image;
    }

    /**
     * Voltear imagen (flip/mirror)
     */
    public function flip(string $imagePath, string $direction = 'horizontal'): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        // En v3.11: 'horizontal' o 'vertical'
        $image->flip($direction);

        return $image;
    }

    /**
     * Crear efecto espejo
     */
    public function mirror(string $imagePath, string $direction = 'horizontal'): ImageInterface
    {
        return $this->flip($imagePath, $direction);
    }

    /**
     * Agregar marca de agua
     */
    public function watermark(
        string $imagePath,
        string $watermarkPath,
        string $position = 'bottom-right',
        int $offsetX = 10,
        int $offsetY = 10,
        int $opacity = 100
    ): ImageInterface {
        $image = $this->manager->read($imagePath);
        $watermark = $this->manager->read($watermarkPath);

        // Ajustar opacidad si es necesario
        if ($opacity < 100) {
            $watermark->reduceColors(255, $opacity);
        }

        $image->place($watermark, $position, $offsetX, $offsetY);

        return $image;
    }

    /**
     * Aplicar filtros diversos
     */
    public function applyFilter(string $imagePath, string $filter, mixed $intensity = null): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        switch (strtolower($filter)) {
            case 'grayscale':
            case 'greyscale':
                $image->greyscale();
                break;

            case 'sepia':
                $image->greyscale();
                // Aplicar tinte sepia
                $image->colorize(25, 15, 5);
                break;

            case 'blur':
                $blur = is_numeric($intensity) ? $intensity : 5;
                $image->blur($blur);
                break;

            case 'brightness':
                $brightness = is_numeric($intensity) ? $intensity : 10;
                $image->brightness($brightness);
                break;

            case 'contrast':
                $contrast = is_numeric($intensity) ? $intensity : 10;
                $image->contrast($contrast);
                break;

            case 'gamma':
                $gamma = is_numeric($intensity) ? $intensity : 1.2;
                $image->gamma($gamma);
                break;

            case 'invert':
                $image->invert();
                break;

            case 'pixelate':
                $pixels = is_numeric($intensity) ? $intensity : 10;
                $image->pixelate($pixels);
                break;

            case 'sharpen':
                $sharpen = is_numeric($intensity) ? $intensity : 10;
                $image->sharpen($sharpen);
                break;

            case 'colorize':
                // Espera array [r, g, b]
                if (is_array($intensity) && count($intensity) >= 3) {
                    $image->colorize($intensity[0], $intensity[1], $intensity[2]);
                }
                break;
        }

        return $image;
    }

    /**
     * Comprimir y cambiar formato
     */
    public function compress(string $imagePath, string $format = 'jpg', int $quality = 85): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        // En v3.11, el formato se especifica al guardar/codificar
        return $image;
    }

    /**
     * Cambiar formato de imagen
     */
    public function convertFormat(string $imagePath, string $newFormat): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        // El formato se define al momento de encode/save
        return $image;
    }

    /**
     * Redimensionar para thumbnails
     */
    public function thumbnail(string $imagePath, int $size = 150): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        // contain() ajusta la imagen dentro del tamaño sin distorsionar
        $image->contain($size, $size);

        return $image;
    }

    /**
     * Añadir texto a la imagen
     */
    public function addText(
        string $imagePath,
        string $text,
        int $x = 10,
        int $y = 10,
        int $size = 16,
        string $color = '000000',
        ?string $fontPath = null
    ): ImageInterface {
        $image = $this->manager->read($imagePath);

        $image->text($text, $x, $y, function (FontFactory $font) use ($size, $color, $fontPath) {
            $font->size($size);
            $font->color($color);
            if ($fontPath && file_exists($fontPath)) {
                $font->file($fontPath);
            }
        });

        return $image;
    }

    /**
     * Guardar imagen con formato específico
     */
    public function save(ImageInterface $image, string $path, string $format = null, int $quality = 90): bool
    {
        // Detectar formato por extensión si no se especifica
        if (!$format) {
            $format = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }

        try {
            switch (strtolower($format)) {
                case 'jpg':
                case 'jpeg':
                    $image->toJpeg($quality)->save($path);
                    break;

                case 'png':
                    $image->toPng()->save($path);
                    break;

                case 'webp':
                    $image->toWebp($quality)->save($path);
                    break;

                case 'gif':
                    $image->toGif()->save($path);
                    break;

                case 'bmp':
                    $image->toBmp()->save($path);
                    break;

                case 'avif':
                    $image->toAvif($quality)->save($path);
                    break;

                default:
                    $image->save($path);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener imagen como respuesta HTTP
     */
    public function response(ImageInterface $image, string $format = 'jpg', int $quality = 90): \Illuminate\Http\Response
    {
        $mimeType = $this->getMimeType($format);

        switch (strtolower($format)) {
            case 'jpg':
            case 'jpeg':
                $content = $image->toJpeg($quality)->toString();
                break;

            case 'png':
                $content = $image->toPng()->toString();
                break;

            case 'webp':
                $content = $image->toWebp($quality)->toString();
                break;

            case 'gif':
                $content = $image->toGif()->toString();
                break;

            default:
                $content = $image->toJpeg($quality)->toString();
                $mimeType = 'image/jpeg';
                break;
        }

        return response($content)->header('Content-Type', $mimeType);
    }

    /**
     * Procesamiento en lote con múltiples operaciones
     */
    public function processImage(string $imagePath, array $operations = []): ImageInterface
    {
        $image = $this->manager->read($imagePath);

        foreach ($operations as $operation => $params) {
            switch ($operation) {
                case 'resize':
                    if ($params['maintain_aspect'] ?? true) {
                        $image->scale($params['width'], $params['height']);
                    } else {
                        $image->resize($params['width'], $params['height']);
                    }
                    break;

                case 'crop':
                    if (isset($params['x']) && isset($params['y'])) {
                        $image->crop($params['width'], $params['height'], $params['x'], $params['y']);
                    } else {
                        $image->cover($params['width'], $params['height']);
                    }
                    break;

                case 'rotate':
                    $image->rotate($params['angle'], $params['background'] ?? 'ffffff');
                    break;

                case 'flip':
                    $image->flip($params['direction'] ?? 'horizontal');
                    break;

                case 'filter':
                    $this->applyFilterToImage($image, $params['type'], $params['intensity'] ?? null);
                    break;

                case 'watermark':
                    $watermark = $this->manager->read($params['path']);
                    $image->place(
                        $watermark,
                        $params['position'] ?? 'bottom-right',
                        $params['x'] ?? 10,
                        $params['y'] ?? 10
                    );
                    break;

                case 'text':
                    $image->text(
                        $params['text'],
                        $params['x'] ?? 10,
                        $params['y'] ?? 10,
                        function (FontFactory $font) use ($params) {
                            $font->size($params['size'] ?? 16);
                            $font->color($params['color'] ?? '000000');
                        }
                    );
                    break;
            }
        }

        return $image;
    }

    /**
     * Aplicar filtro a imagen existente (método auxiliar)
     */
    private function applyFilterToImage(ImageInterface $image, string $filter, mixed $intensity = null): void
    {
        switch (strtolower($filter)) {
            case 'grayscale':
                $image->greyscale();
                break;
            case 'sepia':
                $image->greyscale();
                $image->colorize(25, 15, 5);
                break;
            case 'blur':
                $image->blur($intensity ?? 5);
                break;
            case 'brightness':
                $image->brightness($intensity ?? 10);
                break;
            case 'contrast':
                $image->contrast($intensity ?? 10);
                break;
            case 'invert':
                $image->invert();
                break;
            case 'pixelate':
                $image->pixelate($intensity ?? 10);
                break;
            case 'sharpen':
                $image->sharpen($intensity ?? 10);
                break;
        }
    }

    /**
     * Obtener MIME type por formato
     */
    private function getMimeType(string $format): string
    {
        return match (strtolower($format)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'avif' => 'image/avif',
            default => 'image/jpeg'
        };
    }
}

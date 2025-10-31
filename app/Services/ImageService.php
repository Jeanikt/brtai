<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageService
{
    public function uploadEventBanner(UploadedFile $image, string $userId): string
    {
        try {
            Log::info('Iniciando upload de imagem', [
                'user_id' => $userId,
                'file_name' => $image->getClientOriginalName(),
                'file_size' => $image->getSize(),
                'file_type' => $image->getMimeType()
            ]);

            if (!$this->validateImage($image)) {
                throw new \Exception('Imagem inválida ou muito grande');
            }

            $path = $this->generateEventBannerPath($image, $userId);

            Log::info('Tentando fazer upload para o caminho: ' . $path);

            $uploadSuccess = Storage::disk('supabase')->put(
                $path,
                file_get_contents($image),
                ['visibility' => 'public']
            );

            if (!$uploadSuccess) {
                throw new \Exception('Falha ao fazer upload - retorno falso do storage');
            }

            $url = $this->getImageUrl($path);

            Log::info('Upload realizado com sucesso', [
                'path' => $path,
                'url' => $url
            ]);

            return $url;
        } catch (\Exception $e) {
            Log::error('Erro no upload da imagem: ' . $e->getMessage(), [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Falha ao fazer upload da imagem para o Supabase Storage: ' . $e->getMessage());
        }
    }

    public function deleteImage(string $imageUrl): bool
    {
        try {
            $path = $this->extractPathFromUrl($imageUrl);

            if (!$path) {
                Log::warning('Não foi possível extrair path da URL: ' . $imageUrl);
                return false;
            }

            if (Storage::disk('supabase')->exists($path)) {
                $deleted = Storage::disk('supabase')->delete($path);
                Log::info('Imagem deletada', ['path' => $path, 'deleted' => $deleted]);
                return $deleted;
            }

            Log::warning('Arquivo não encontrado no storage: ' . $path);
            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar imagem: ' . $e->getMessage());
            return false;
        }
    }

    public function getImageUrl(string $path): string
    {
        $baseUrl = config('filesystems.disks.supabase.url');
        return $baseUrl . '/' . $path;
    }

    private function generateEventBannerPath(UploadedFile $image, string $userId): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        $extension = strtolower($image->getClientOriginalExtension());

        return "{$userId}/banner_{$timestamp}_{$random}.{$extension}";
    }

    private function extractPathFromUrl(string $url): ?string
    {
        $baseUrl = config('filesystems.disks.supabase.url');

        Log::info('Extraindo path da URL', [
            'url' => $url,
            'base_url' => $baseUrl
        ]);

        if (str_starts_with($url, $baseUrl)) {
            $path = str_replace($baseUrl . '/', '', $url);
            Log::info('Path extraído (base_url): ' . $path);
            return $path;
        }

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        if (str_contains($path, '/storage/v1/object/public/')) {
            $extractedPath = str_replace('/storage/v1/object/public/' . env('SUPABASE_BUCKET', 'events') . '/', '', $path);
            Log::info('Path extraído (storage_path): ' . $extractedPath);
            return $extractedPath;
        }

        $cleanedPath = ltrim($path, '/');
        Log::info('Path extraído (cleaned): ' . $cleanedPath);
        return $cleanedPath;
    }

    public function validateImage(UploadedFile $image): bool
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 10 * 1024 * 1024;

        $isValidMime = in_array($image->getMimeType(), $allowedMimes);
        $isValidSize = $image->getSize() <= $maxSize;

        Log::info('Validação de imagem', [
            'mime_type' => $image->getMimeType(),
            'size' => $image->getSize(),
            'is_valid_mime' => $isValidMime,
            'is_valid_size' => $isValidSize
        ]);

        return $isValidMime && $isValidSize;
    }

    public function testConnection(): bool
    {
        try {
            $testPath = 'test-connection.txt';
            $content = 'Test connection at ' . now()->toISOString();

            $uploaded = Storage::disk('supabase')->put($testPath, $content);

            if ($uploaded) {
                $exists = Storage::disk('supabase')->exists($testPath);
                $url = $this->getImageUrl($testPath);

                Log::info('Teste de conexão com Supabase', [
                    'uploaded' => $uploaded,
                    'exists' => $exists,
                    'url' => $url
                ]);

                Storage::disk('supabase')->delete($testPath);
                return true;
            }

            Log::error('Teste de conexão com Supabase falhou - upload não realizado');
            return false;
        } catch (\Exception $e) {
            Log::error('Teste de conexão com Supabase falhou: ' . $e->getMessage());
            return false;
        }
    }

    public function listFiles(string $path = ''): array
    {
        try {
            $files = Storage::disk('supabase')->files($path);
            Log::info('Arquivos no storage', ['path' => $path, 'files' => $files]);
            return $files;
        } catch (\Exception $e) {
            Log::error('Erro ao listar arquivos: ' . $e->getMessage());
            return [];
        }
    }
}

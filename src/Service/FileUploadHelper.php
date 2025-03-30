<?php
// src/Service/FileUploadHelper.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploadHelper
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * Handles student card upload for candidate profiles
     *
     * @param UploadedFile|null $file The uploaded file
     * @param object $user The user entity
     * @param object $candidateProfile The candidate profile entity
     * @return void
     * @throws \RuntimeException When upload fails
     */
    public function handleStudentCardUpload(?UploadedFile $file, $user, $candidateProfile): void
    {
        if (!$file) {
            return;
        }

        // Create user directory if it doesn't exist
        $userDirectory = $this->params->get('student_card_directory') . '/' . $user->getId();

        if (!file_exists($userDirectory)) {
            // Create the directory with private permissions (0700)
            if (!mkdir($userDirectory, 0700, true)) {
                throw new \RuntimeException('Unable to create the upload directory: ' . $userDirectory);
            }
        }

        // Generate a secure filename
        $extension = $file->guessExtension() ?: 'bin';
        $newFilename = 'card_' . uniqid() . '.' . $extension;

        try {
            // Move the file to the private directory
            $file->move(
                $userDirectory,
                $newFilename
            );

            // Store the relative path in the database
            $relativePath = $user->getId() . '/' . $newFilename;
            $candidateProfile->setStudentCard($relativePath);

            // Set read-only permissions on the uploaded file for extra security
            chmod($userDirectory . '/' . $newFilename, 0400);
        } catch (FileException $e) {
            throw new \RuntimeException('Error uploading file: ' . $e->getMessage());
        }
    }

    /**
     * Generic file upload method for any type of file
     *
     * @param UploadedFile|null $file The uploaded file
     * @param string $targetDirectory The target directory (parameter name or absolute path)
     * @param string $filenamePrefix Optional prefix for the filename
     * @return string|null The relative path to the file or null if no file
     * @throws \RuntimeException When upload fails
     */
    public function uploadFile(?UploadedFile $file, string $targetDirectory, string $filenamePrefix = ''): ?string
    {
        if (!$file) {
            return null;
        }

        // Resolve directory from parameter if it starts with %
        if (str_starts_with($targetDirectory, '%') && str_ends_with($targetDirectory, '%')) {
            $paramName = substr($targetDirectory, 1, -1);
            $targetDirectory = $this->params->get($paramName);
        }

        // Create directory if it doesn't exist
        if (!file_exists($targetDirectory)) {
            if (!mkdir($targetDirectory, 0755, true)) {
                throw new \RuntimeException('Unable to create the upload directory: ' . $targetDirectory);
            }
        }

        // Create secure filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Sanitize filename
        $safeFilename = preg_replace('/[^a-z0-9_-]/i', '_', $originalFilename);
        $extension = $file->guessExtension() ?: 'bin';
        $newFilename = $filenamePrefix . $safeFilename . '_' . uniqid() . '.' . $extension;

        try {
            $file->move($targetDirectory, $newFilename);
            return $newFilename;
        } catch (FileException $e) {
            throw new \RuntimeException('Error uploading file: ' . $e->getMessage());
        }
    }
}
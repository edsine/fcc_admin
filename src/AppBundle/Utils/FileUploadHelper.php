<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/27/2016
 * Time: 12:16 PM
 */

namespace AppBundle\Utils;


use AppBundle\AppException\AppException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadHelper
{
    public function getRandomFileNameSuffix(){
        return '_' . date('YmdHis');
    }

    public function getBaseDirectory()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return AppConstants::DEV_UPLOAD_BASE_DIR;
        } else {
            return AppConstants::PROD_UPLOAD_BASE_DIR;
        }
    }

    public function getNominalRollUploadDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_NOMINAL_ROLL_UPLOAD_DIR
                : (AppConstants::DEV_NOMINAL_ROLL_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_NOMINAL_ROLL_UPLOAD_DIR
                : (AppConstants::PROD_NOMINAL_ROLL_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function getVacancyUploadDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_VACANCY_UPLOAD_DIR
                : (AppConstants::DEV_VACANCY_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_VACANCY_UPLOAD_DIR
                : (AppConstants::PROD_VACANCY_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function getProfileUploadDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_PROFILE_PHOTO_UPLOAD_DIR
                : (AppConstants::DEV_PROFILE_PHOTO_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_PROFILE_PHOTO_UPLOAD_DIR
                : (AppConstants::PROD_PROFILE_PHOTO_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function getResourceDownloadsDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_DOWNLOAD_MANAGER_UPLOAD_DIR
                : (AppConstants::DEV_DOWNLOAD_MANAGER_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_DOWNLOAD_MANAGER_UPLOAD_DIR
                : (AppConstants::PROD_DOWNLOAD_MANAGER_UPLOAD_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function getPublicVacancyUploadUrl()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return AppConstants::DEV_PUBLIC_VACANCY_UPLOAD_URL;
        } else {
            return AppConstants::PROD_PUBLIC_VACANCY_UPLOAD_URL;
        }
    }

    public function getPublicTrashUrl()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return AppConstants::DEV_PUBLIC_UPLOAD_TRASH_URL;
        } else {
            return AppConstants::PROD_PUBLIC_UPLOAD_TRASH_URL;
        }
    }

    public function getDownloadUrlPrefix()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return AppConstants::DEV_PUBLIC_UPLOAD_BASE_DIR;
        } else {
            return AppConstants::PROD_PUBLIC_UPLOAD_BASE_DIR;
        }
    }

    public function getProfileUrl()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return AppConstants::DEV_PUBLIC_PROFILE_URL;
        } else {
            return AppConstants::PROD_PUBLIC_PROFILE_URL;
        }
    }

    public function getTrashDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_UPLOAD_TRASH_DIR
                : (AppConstants::DEV_UPLOAD_TRASH_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_UPLOAD_TRASH_DIR
                : (AppConstants::PROD_UPLOAD_TRASH_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function getPublicTrashDirectory($destinationDir = "")
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return (empty($destinationDir)
                ? AppConstants::DEV_PUBLIC_UPLOAD_TRASH_DIR
                : (AppConstants::DEV_PUBLIC_UPLOAD_TRASH_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        } else {
            return (empty($destinationDir)
                ? AppConstants::PROD_PUBLIC_UPLOAD_TRASH_DIR
                : (AppConstants::PROD_PUBLIC_UPLOAD_TRASH_DIR . $destinationDir . DIRECTORY_SEPARATOR));
        }
    }

    public function uploadFile(UploadedFile $uploadedFile, $destinationDirectory, $fileNameWithExtension)
    {
        $fileObj = null;
        try {
            $fileObj = $uploadedFile->move($destinationDirectory, $fileNameWithExtension);
        } catch (\Exception $exc) {
            throw new AppException($exc->getMessage());
        }

        return $fileObj;
    }

    public function removeUploadedFile($srcDirectory, $fileNameWithExtension, $trashDirectory)
    {
        try {
            $fs = new Filesystem();

            if (!file_exists($trashDirectory)) {
                $fs->mkdir($trashDirectory);
            }

            $fs->rename($srcDirectory . $fileNameWithExtension, $trashDirectory . $fileNameWithExtension);
        } catch (\Exception $exc) {
            //throw new AppException($exc->getMessage());
        }
    }

}
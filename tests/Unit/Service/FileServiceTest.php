<?php
// tests/Unit/Api/Service/FileServiceTest.php
namespace App\Tests\Unit\Api\Service;

use App\Api\Service\FileService;
use App\Api\Service\UserService;
use App\Storage\Repository\FileRepository;
use App\Storage\Entity\File;
use App\Storage\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // <-- ADD THIS USE STATEMENT

class FileServiceTest extends TestCase
{
    private $mockFileRepository;
    private $mockRequestStack;
    private $mockUserService;
    private $mockFileEntity;
    private $mockUserEntity;
    private $mockHttpFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockFileRepository = $this->createMock(FileRepository::class);
        $this->mockRequestStack = $this->createMock(RequestStack::class);
        $this->mockUserService = $this->createMock(UserService::class);

        // --- Mocks for File Entity ---
        $this->mockFileEntity = $this->createMock(File::class);
        $this->mockFileEntity->method('getOriginalFilename')->willReturn('test_document.pdf');
        $this->mockFileEntity->method('getFileUuid')->willReturn('f1e2d3c4-b5a6-4789-abcd-567890abcdef');
        $this->mockFileEntity->method('getSize')->willReturn(1024);
        $this->mockFileEntity->method('getMimeType')->willReturn('application/pdf');
        $this->mockFileEntity->method('getStoragePath')->willReturn('/uploads/some/path/test_document.pdf');

        // --- Mocks for User Entity ---
        $this->mockUserEntity = $this->createMock(User::class);
        $this->mockUserEntity->method('getUuid')->willReturn('u1s2e3r4-i5d6-7890-abcd-1234567890abcd');
        $this->mockUserEntity->method('getEmail')->willReturn('test.user@example.com');

        // --- Mocks for UserService ---
        // Your FileService calls this: $this->userService->findUserByUuid($userUuid);
        $this->mockUserService->method('findUserByUuid')
                              ->willReturn($this->mockUserEntity);

        // --- Mock for Symfony\Component\HttpFoundation\File\File (returned by UploadedFile::move()) ---
        $this->mockHttpFile = $this->createMock(HttpFile::class);
    }

    public function testUploadFileSuccessfully(): void
    {
        $directory = '/tmp/uploads/';
        $userUuid = 'u1s2e3r4-i5d6-7890-abcd-1234567890abcd';

        $mockUploadedFile = $this->createMock(UploadedFile::class);
        $mockUploadedFile->method('getClientOriginalName')->willReturn('my_document.pdf');
        $mockUploadedFile->method('getClientMimeType')->willReturn('application/pdf');
        $mockUploadedFile->method('getSize')->willReturn(2048);
        $mockUploadedFile->method('guessExtension')->willReturn('pdf');
        $mockUploadedFile->method('move')->willReturn($this->mockHttpFile);

        $this->mockUserService->expects($this->once())
                              ->method('findUserByUuid')
                              ->with($userUuid)
                              ->willReturn($this->mockUserEntity);

        $this->mockFileRepository->expects($this->once())
                                 ->method('saveFile')
                                 ->with($this->callback(function(array $fileData) {
                                     $this->assertArrayHasKey('originalFilename', $fileData);
                                     $this->assertEquals('my_document.pdf', $fileData['originalFilename']);
                                     $this->assertArrayHasKey('size', $fileData);
                                     $this->assertEquals(2048, $fileData['size']);
                                     $this->assertArrayHasKey('mimeType', $fileData);
                                     $this->assertEquals('application/pdf', $fileData['mimeType']);
                                     $this->assertArrayHasKey('storagePath', $fileData);
                                     $this->assertIsString($fileData['storagePath']);
                                     return true;
                                 }), $this->mockUserEntity)
                                 ->willReturn($this->mockFileEntity);

        $fileService = new FileService(
            $this->mockRequestStack,
            $this->mockFileRepository,
            $this->mockUserService
        );

        $fileService->uploadFile($mockUploadedFile, $directory, $userUuid);
    }

    public function testGetAllUserFiles(): void
    {
        $user = $this->mockUserEntity;

        $this->mockFileRepository->expects($this->once())
                                 ->method('findAllUserFiles')
                                 ->with($user)
                                 ->willReturn([$this->mockFileEntity, $this->createMock(File::class)]);

        $fileService = new FileService(
            $this->mockRequestStack,
            $this->mockFileRepository,
            $this->mockUserService
        );

        $files = $fileService->findAllUserFiles($user);

        $this->assertIsArray($files);
        $this->assertCount(2, $files);
        $this->assertInstanceOf(File::class, $files[0]);
    }

    public function testDeleteFileSuccessfully(): void
    {
        $storagePath = 'test_document.pdf';
        $directory = '/tmp/uploads/';
        $userUuid = 'u1s2e3r4-i5d6-7890-abcd-1234567890abcd';

        $this->mockFileRepository->expects($this->once())
                                 ->method('findOneByUserUuidAndFilePath')
                                 ->with($userUuid, $storagePath)
                                 ->willReturn($this->mockFileEntity);

        $this->mockFileRepository->expects($this->once())
                                 ->method('deleteFile')
                                 ->with($this->mockFileEntity)
                                 ->willReturn(null);

        $fileService = new FileService(
            $this->mockRequestStack,
            $this->mockFileRepository,
            $this->mockUserService
        );

        $result = $fileService->deleteFile($storagePath, $directory, $userUuid);

        $this->assertTrue($result);
    }

    // --- NEW TEST METHOD ADDED BELOW ---
    public function testDownloadFileNotFoundInRepositoryThrowsException(): void
    {
        $storagePath = 'non_existent_file.txt';
        $directory = '/tmp/uploads/';
        $userUuid = 'u1s2e3r4-i5d6-7890-abcd-1234567890abcd';

        // Configure FileRepository mock: findOneByUserUuidAndFilePath returns null
        $this->mockFileRepository->expects($this->once())
                                 ->method('findOneByUserUuidAndFilePath')
                                 ->with($userUuid, $storagePath)
                                 ->willReturn(null); // File not found

        // Expect that a NotFoundHttpException will be thrown
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File not found.');

        $fileService = new FileService(
            $this->mockRequestStack,
            $this->mockFileRepository,
            $this->mockUserService
        );

        $fileService->downloadFile($storagePath, $directory, $userUuid);
    }

    public function testDownloadFilePhysicalFileNotFoundThrowsException(): void
    {
        $storagePath = 'existing_record_missing_physical_file.pdf';
        $userUuid = 'u1s2e3r4-i5d6-7890-abcd-1234567890abcd';
        // Use a directory that does NOT exist or where you control its contents for unit tests
        // Using a non-existent temp directory to ensure file_exists returns false
        $directory = '/tmp/nonexistent_test_uploads/';

        // Configure FileRepository mock: it finds the file record
        $this->mockFileRepository->expects($this->once())
                                 ->method('findOneByUserUuidAndFilePath')
                                 ->with($userUuid, $storagePath)
                                 ->willReturn($this->mockFileEntity); // File record is found in DB

        // If your FileService had a Filesystem injected:
        // $this->mockFilesystem->expects($this->once())
        //                      ->method('exists')
        //                      ->with($directory . $userUuid . "/" . $storagePath)
        //                      ->willReturn(false);

        // Expect that a NotFoundHttpException will be thrown due to physical file absence
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The file does not physically exist.');

        $fileService = new FileService(
            $this->mockRequestStack,
            $this->mockFileRepository,
            $this->mockUserService
            // , $this->mockFilesystem // Uncomment if Filesystem is injected
        );

        // Your FileService::downloadFile method call
        $fileService->downloadFile($storagePath, $directory, $userUuid);
    }
}
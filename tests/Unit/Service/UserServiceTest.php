<?php
// tests/Unit/Service/UserServiceTest.php
namespace App\Tests\Unit\Service;

use App\Api\Service\UserService;
use App\Storage\Repository\UserRepository;
use App\Storage\Entity\User;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private $mockUserRepository;
    private $mockUserEntity; // To represent a mocked User object

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockUserRepository = $this->createMock(UserRepository::class);
        $this->mockUserEntity = $this->createMock(User::class);

        $this->mockUserEntity->method('getUuid')->willReturn('a1b2c3d4-e5f6-4789-abcd-567890abcdef');
        $this->mockUserEntity->method('getEmail')->willReturn('test.user@example.com');
    }

    public function testGetUserByUuidReturnsCorrectUser(): void
    {
        $uuid = 'a1b2c3d4-e5f6-4789-abcd-567890abcdef';

        $this->mockUserRepository->expects($this->once())
                                 ->method('findOneByUuid')
                                 ->with($uuid)
                                 ->willReturn($this->mockUserEntity);

        $userService = new UserService($this->mockUserRepository);

        $user = $userService->findUserByUuid($uuid);

        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($uuid, $user->getUuid());
        $this->assertEquals('test.user@example.com', $user->getEmail());
    }

    public function testGetUserByUuidReturnsNullForNonExistentUser(): void
    {
        $nonExistentUuid = 'ffffffff-eeee-4444-aaaa-111122223333';

        $this->mockUserRepository->expects($this->once())
                                 ->method('findOneByUuid')
                                 ->with($nonExistentUuid)
                                 ->willReturn(null);

        $userService = new UserService($this->mockUserRepository);

        $user = $userService->findUserByUuid($nonExistentUuid);

        $this->assertNull($user);
    }

    // --- UPDATED TEST: Test isExistByEmail when email exists ---
    public function testIsExistByEmailReturnsTrueForExistingEmail(): void
    {
        $email = 'existing@example.com';

        // CHANGE THIS LINE: Configure UserRepository mock to return a single User entity, not an array
        $this->mockUserRepository->expects($this->once())
                                 ->method('findByEmail') // Still calling findByEmail as per your service
                                 ->with($email)
                                 ->willReturn($this->mockUserEntity); // <-- RETURN THE MOCKED USER DIRECTLY

        $userService = new UserService($this->mockUserRepository);

        $result = $userService->isExistByEmail($email);

        $this->assertTrue($result);
    }

    // --- UPDATED TEST: Test isExistByEmail when email does NOT exist ---
    public function testIsExistByEmailReturnsFalseForNonExistentEmail(): void
    {
        $email = 'nonexistent@example.com';

        // Configure UserRepository mock: findByEmail returns null
        $this->mockUserRepository->expects($this->once())
                                 ->method('findByEmail')
                                 ->with($email)
                                 ->willReturn(null); // Correctly returns null when not found

        $userService = new UserService($this->mockUserRepository);

        $result = $userService->isExistByEmail($email);

        $this->assertFalse($result);
    }
    
    public function testSaveUserSuccessfully(): void
    {
        // Configure the UserRepository mock to expect saveUser to be called once with a User object
        $this->mockUserRepository->expects($this->once())
                                 ->method('saveUser')
                                 ->with($this->mockUserEntity); // Expect it to be called with the mocked User entity

        $userService = new UserService($this->mockUserRepository);

        // Call the saveUser method in the service
        $userService->saveUser($this->mockUserEntity);

        // No return value from saveUser, so we just assert that the mock method was called as expected.
        // PHPUnit's expects($this->once()) takes care of the assertion.
    }
}
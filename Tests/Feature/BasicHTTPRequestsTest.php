<?php

namespace Tests\Feature;

use Apiz\Http\Response;
use PHPUnit\Framework\TestCase;
use Tests\Feature\Mocks\BasicHTTPAPI;

class BasicHTTPRequestsTest extends TestCase
{
    public function testGet()
    {
        $exampleAPI = $this->getBasicHTTPAPIMock();
        $users = $exampleAPI->getAllUsers();

        $this->assertInstanceOf(Response::class, $users);
    }

    public function testPost()
    {
        $exampleAPI = $this->getBasicHTTPAPIMock();
        $user = $exampleAPI->createUser([
            'name' => "John Doe",
            'designation' => "Spy"
        ]);

        $this->assertInstanceOf(Response::class, $user);
    }

    public function testPut()
    {
        $exampleAPI = $this->getBasicHTTPAPIMock();
        $user = $exampleAPI->updateUser([
            'name' => "Jane Doe",
            'designation' => "Super Spy"
        ]);

        $this->assertInstanceOf(Response::class, $user);
    }

    public function testPatch()
    {
        $exampleAPI = $this->getBasicHTTPAPIMock();
        $user = $exampleAPI->partiallyUpdateUser([
            'name' => "James Bond"
        ]);

        $this->assertInstanceOf(Response::class, $user);
    }

    public function testDelete()
    {
        $exampleAPI = $this->getBasicHTTPAPIMock();
        $user = $exampleAPI->deleteUser(2);

        $this->assertInstanceOf(Response::class, $user);
    }

    /**
     * @return BasicHTTPAPI
     */
    protected function getBasicHTTPAPIMock()
    {
        return new BasicHTTPAPI();
    }
}
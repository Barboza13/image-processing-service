<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configure the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    /**
     * Test get all images.
     *
     * @return void
     */
    public function test_get_all_images(): void
    {
        $response = $this->getJson("api/images");

        $response->assertStatus(200);
        $this->assertIsArray($response->json("images"));
    }

    /**
     * Test save new image.
     *
     * @return void
     */
    public function test_store_image(): void
    {
        $image = UploadedFile::fake()->image("proof_image.jpg");

        $response = $this->postJson("api/images", [
            "user_id" => 1,
            "name" => "proof_image",
            "image" => $image
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(["image", "message"]);
    }

    /**
     * Test store new image with exists name.
     *
     * @return void
     */
    public function test_store_image_with_exists_name(): void
    {
        Image::create([
            "name" => "proof_image",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $image = UploadedFile::fake()->image("proof_image.jpg");

        $response = $this->postJson("api/images", [
            "user_id" => 1,
            "name" => "proof_image",
            "image" => $image,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(["message"]);
    }

    /**
     * Test show specified image data.
     *
     * @return void
     */
    public function test_show_image(): void
    {
        $image_data = Image::create([
            "name" => "proof_name",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $response = $this->getJson("api/images/$image_data->id");

        $response->assertStatus(200)
            ->assertJsonStructure(["image"]);
    }

    /**
     * Test update specified image.
     *
     * @return void
     */
    public function test_update_image(): void
    {
        $image_data = Image::create([
            "name" => "proof_image2",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $new_image = UploadedFile::fake()->image("new_image.jpg");

        $response = $this->putJson(
            "api/images/$image_data->id",
            [
                "name" => "new_name",
                "user_id" => 1,
                "image" => $new_image
            ]
        );

        $response->assertStatus(200)
            ->assertJsonStructure(["image", "message"]);
    }

    /**
     * Test update image data without image.
     *
     * @return void
     */
    public function test_upate_image_data_without_image(): void
    {
        $image_data = Image::create([
            "name" => "proof_image3",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $response = $this->putJson(
            "api/images/$image_data->id",
            [
                "name" => "new_name",
                "user_id" => 1,
            ]
        );

        $response->assertStatus(200)
            ->assertJsonStructure(["image", "message"]);
    }

    /**
     * Test update image data with exists name.
     *
     * @return void
     */
    public function test_update_image_data_with_exists_name(): void
    {
        // Image data with used name.
        Image::create([
            "name" => "proof_name4",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        // Image data to be updated.
        $image_data = Image::create([
            "name" => "proof_name5",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $response = $this->putJson(
            "api/images/$image_data->id",
            [
                "name" => "proof_name4",
                "user_id" => 1,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonStructure(["message"]);
    }

    /**
     * Test delete specified image.
     *
     * @return void
     */
    public function test_delete_image(): void
    {
        $image_data = Image::create([
            "name" => "proof_image2",
            "user_id" => 1,
            "size" => 15 * 1024,
            "height" => 768,
            "width" => 1366,
            "format" => "jpg"
        ]);

        $response = $this->deleteJson("api/images/$image_data->id");

        $response->assertStatus(200);
        $this->assertSoftDeleted($image_data);
    }
}

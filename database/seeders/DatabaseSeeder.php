<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    private const SAMPLE_JPEG_BASE64 = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8QDw8PEA8PDw8QDw8PDw8QFREWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGhAQGy0mICYtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAXAAEBAQEAAAAAAAAAAAAAAAAAAQID/8QAFhEBAQEAAAAAAAAAAAAAAAAAAQAC/9oADAMBAAIQAxAAAAGjAqf/xAAbEAACAwADAAAAAAAAAAAAAAABAgADEQQSIf/aAAgBAQABBQK8V3E6x5Yw2f/EABYRAQEBAAAAAAAAAAAAAAAAAAABEf/aAAgBAwEBPwGn/8QAFhEBAQEAAAAAAAAAAAAAAAAAAQAR/9oACAECAQE/AYf/xAAaEAADAQEBAQAAAAAAAAAAAAABAhEAMUFh/9oACAEBAAY/ArV5Q2GQf//EABwQAQADAQEBAQAAAAAAAAAAAAEAESExQVFhcf/aAAgBAQABPyG4mPIZ0XnqM4zjO+0wI2K5H//aAAwDAQACAAMAAAAQ8//EABYRAQEBAAAAAAAAAAAAAAAAAAABEf/aAAgBAwEBPxCQf//EABcRAQEBAQAAAAAAAAAAAAAAAAERACH/2gAIAQIBAT8QjIv/xAAdEAEAAwEAAwAAAAAAAAAAAAABABEhMUFRYXGB/9oACAEBAAE/EE2zj4tt3j6FfGLr2u0V2qY1E1l3Qd5Jr3fZs4q5P/2Q==';

    public function run(): void
    {
        $this->ensureSampleImagesExist();

        $userTeste = User::factory()->create([
            'name' => 'Usuário Teste',
            'username' => 'usuarioteste',
            'email' => 'teste@instaclone.com',
        ]);

        $users = User::factory(15)->create();
        $users->push($userTeste);

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $post = Post::create([
                    'user_id' => $user->id,
                    'image_url' => 'posts/sample_image_' . rand(1, 5) . '.jpg',
                    'caption' => 'Este é um post de teste criado automaticamente! #' . rand(100, 999),
                ]);

                $randomLikers = $users->random(3);
                foreach ($randomLikers as $liker) {
                    Like::firstOrCreate([
                        'user_id' => $liker->id,
                        'post_id' => $post->id
                    ]);
                }

                $randomCommenters = $users->random(2);
                foreach ($randomCommenters as $commenter) {
                    Comment::create([
                        'user_id' => $commenter->id,
                        'post_id' => $post->id,
                        'body' => 'Muito legal essa publicação! 👋',
                    ]);
                }
            }

            $usersToFollow = $users->where('id', '!=', $user->id)->random(4);
            foreach ($usersToFollow as $followedUser) {
                $user->following()->attach($followedUser->id);
            }
        }
    }

    private function ensureSampleImagesExist(): void
    {
        $imageBinary = base64_decode(self::SAMPLE_JPEG_BASE64, true);

        if ($imageBinary === false) {
            return;
        }

        for ($index = 1; $index <= 5; $index++) {
            $path = "posts/sample_image_{$index}.jpg";

            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $imageBinary);
            }
        }
    }
}

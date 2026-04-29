<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Database\Seeder;
use

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cria o Usuário de Teste principal
        $userTeste = User::factory()->create([
            'name' => 'Usuário Teste',
            'username' => 'usuarioteste',
            'email' => 'teste@instaclone.com',
        ]);

        // 2. Cria 15 usuários aleatórios
        $users = User::factory(15)->create();
        $users->push($userTeste);

        // 3. Cria Posts, Follows, Likes e Comentários
        foreach ($users as $user) {
            // Cada usuário cria 2 posts
            for ($i = 0; $i < 2; $i++) {
                $post = Post::create([
                    'user_id' => $user->id,
                    'image_url' => 'posts/sample_image_' . rand(1, 5) . '.jpg',
                    'caption' => 'Este é um post de teste criado automaticamente! #' . rand(100, 999),
                ]);

                // 3 usuários aleatórios curtem este post
                $randomLikers = $users->random(3);
                foreach ($randomLikers as $liker) {
                    Like::firstOrCreate([
                        'user_id' => $liker->id,
                        'post_id' => $post->id
                    ]);
                }

                // 2 usuários aleatórios comentam neste post
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
}

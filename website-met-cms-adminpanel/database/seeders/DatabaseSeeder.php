<?php

namespace Database\Seeders;

use App\Models\Build;
use App\Models\Competition;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Skills2026!')
        ]);

        // 4 vaste pagina's

        Page::create([
            'title' => 'Homepage',
            'intro' => 'Dit is de homepagina',
            'description' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Saepe nihil quas aut corrupti dolorem vero deleniti nulla maiores deserunt minima, provident porro aspernatur reprehenderit autem. Minus ullam ex facilis aperiam.',
            'show_in_navigation' => true,
            'image_filename' => 'page-images/JOLcGww8RxbFjqBwpGW0xNbl57UeotbC2dannaC5.png',
            'slug' => 'homepage',
            'tag' => 'homepage'
        ]);

        Page::create([
            'title' => 'Builds',
            'intro' => 'Dit is de buildspagina',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Iste nostrum cupiditate officiis, ab suscipit ipsum adipisci ducimus. Odio quo unde autem officiis quisquam. Beatae quasi temporibus hic et, commodi amet.',
            'show_in_navigation' => true,
            'image_filename' => 'page-images/mf6Sn3PJ6XdyhctwRhw0EhTUBleE3RWOyK7r7g6D.png',
            'slug' => 'builds',
            'tag' => 'builds'
        ]);

        Page::create([
            'title' => 'About',
            'intro' => 'Dit is de aboutpagina',
            'description' => 'Lorem ipsum dolor sit amet consectetur, adipisicing elit. Et voluptatem neque quae obcaecati veniam fuga, dignissimos, quis, doloremque quos necessitatibus eaque facere quidem? Fuga nulla doloribus, voluptates corrupti ex ea.',
            'show_in_navigation' => true,
            'image_filename' => 'page-images/TcvMfRfecp5NKWFmZtK2XFZjqeq54VilqyquY6So.png',
            'slug' => 'about',
            'tag' => 'about'
        ]);

        Page::create([
            'title' => 'Contact',
            'intro' => 'Dit is de contactpagina',
            'description' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Minima nemo soluta veritatis assumenda cumque consequuntur deserunt ab odio iste earum, fugit illo delectus quasi et harum sed, modi voluptatum repudiandae?',
            'show_in_navigation' => true,
            'image_filename' => 'page-images/2meAmYsFVQ27i6bxfbuxgjZgCMPN00eP7sS5TMR3.png',
            'slug' => 'contact'
        ]);

        // 5 builds

        Build::create([
            'title' => 'Build 1',
            'intro' => 'Dit is build 1',
            'description' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Officiis magni consectetur accusamus adipisci laborum eos id iusto beatae similique, at, tempora deleniti, fugiat totam cum ipsa eveniet iure quidem eaque?',
            'active' => true,
            'thumbnail_filename' => '/build-thumbnail-images/processed/QJHUmmNwBJciSSDDvi4MPMsyBM9qucJodhlQ0Nxg.jpg',
            'background_filename' => 'build-background-images/TRAZnRuQbCwP3Q4zPzBAuQG7y75InSQd6rRWrNu7.png',
            'signature_filename' => 'build-signature-images/HYSy1VpnvvV82lJhFj4JmeloNfUR3lfV81NyBBip.jpg',
            'slug' => 'build-1',
        ]);

        Build::create([
            'title' => 'Build 2',
            'intro' => 'Dit is build 2',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident corporis quia aspernatur eius corrupti, sint voluptate delectus a rerum vel consequatur officiis cupiditate id optio dolor dolorem quibusdam facere inventore.',
            'active' => true,
            'thumbnail_filename' => '/build-thumbnail-images/processed/vsisoxyFNqvpAhTiQ0Zdw0ANKlaKQG6oj9QSggRM.jpg',
            'background_filename' => 'build-background-images/avAberqInJAOtkWXteGd8C27coixX2NP9zyut1Na.jpg',
            'signature_filename' => 'build-signature-images/SRWKomMdZdjLOrLuQnoUqIHWmRNKLgpcexN1OVGD.jpg',
            'slug' => 'build-2',
        ]);

        Build::create([
            'title' => 'Build 3',
            'intro' => 'Dit is build 3',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nemo adipisci commodi inventore architecto quidem, ullam laudantium aut porro, ex repudiandae nihil libero numquam in facilis dicta, accusantium recusandae voluptatem odio.',
            'active' => true,
            'thumbnail_filename' => '/build-thumbnail-images/processed/zfsyrx4SMaek1toz3smfB49fWjLxcY8ZZzHtWf5O.jpg',
            'background_filename' => 'build-background-images/NPqe1kdBiHOUA4gr0WkbhHVEbOAGMEI4SOYwppRr.jpg',
            'signature_filename' => 'build-signature-images/qMqDHuKNNi8Barw9lKXWdJElrOQtYhY1YiWWd7NJ.jpg',
            'slug' => 'build-3',
        ]);

        Build::create([
            'title' => 'Build 4',
            'intro' => 'Dit is build 4',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam, voluptatum aperiam? Neque ipsa sed, aspernatur illo aperiam at laboriosam dicta nostrum atque repellendus in, explicabo tempora reiciendis ratione dolorum officia.',
            'active' => true,
            'thumbnail_filename' => '/build-thumbnail-images/processed/GkxWgmO3obaiehfgrSr7aphEI6KqPU7V1W9WF6l7.jpg',
            'background_filename' => 'build-background-images/LFERrjz4z4KGAxBHSmKtaxmpvYDlyXZtUgrPicBO.jpg',
            'signature_filename' => 'build-signature-images/z6uWgXfZeTsEKAhGdUqpgg4cT3wx38FFrGkVGra2.jpg',
            'slug' => 'build-4',
        ]);

        Build::create([
            'title' => 'Build 5',
            'intro' => 'Dit is build 5',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Vitae in nemo nulla nisi laudantium? Nemo repudiandae tenetur adipisci nobis. Dicta eos dolor amet, soluta nulla distinctio! Tenetur magni nihil quam!',
            'active' => true,
            'thumbnail_filename' => '/build-thumbnail-images/processed/bFCA5rgWnZDNSrEtX9p4cWXOvvdJcG9cOea9D3lZ.jpg',
            'background_filename' => 'build-background-images/qIewsggeCUYZ4u1f7t56hdOAmYenXwXBcGEjSwSw.jpg',
            'signature_filename' => 'build-signature-images/O5Lyk0QsVyY9m0FEto8fapIWJO242g5P55TBKdF9.jpg',
            'slug' => 'build-5',
        ]);

        // 3 wedstrijden

        Competition::create([
            'title' => 'Wedstrijd 1',
            'text' => 'Dit is wedstrijd 1',
            'active' => true,
            'image_filename' => 'page-images/JOLcGww8RxbFjqBwpGW0xNbl57UeotbC2dannaC5.png',
            'position' => 1,
        ]);

        Competition::create([
            'title' => 'Wedstrijd 2',
            'text' => 'Dit is wedstrijd 2',
            'active' => true,
            'image_filename' => 'page-images/TcvMfRfecp5NKWFmZtK2XFZjqeq54VilqyquY6So.png',
            'position' => 2,
        ]);

        Competition::create([
            'title' => 'Wedstrijd 3',
            'text' => 'Dit is wedstrijd 3',
            'active' => true,
            'image_filename' => 'page-images/2meAmYsFVQ27i6bxfbuxgjZgCMPN00eP7sS5TMR3.png',
            'position' => 3,
        ]);
    }
}

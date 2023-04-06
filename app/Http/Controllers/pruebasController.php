<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class PruebasController extends Controller{
public function testOrm()
{
    $posts = Post::all();
foreach ($posts as $post) {
    echo '<h1>' . e($post->title) . '</h1>'; 
    echo '<br>';
    echo "<span style= 'color:gray;'>{$post->user->name} - {$post->category->name}</span>";
    echo '<p>' . e($post->content) . '</p>';
    echo '<hr>';
}
die();
}
}


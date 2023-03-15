<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \\Illuminate\\Http\\Response
     */
    public function index()
    {
        $posts = $this->_getDisplayLists();
        return response()->view('posts.index',['posts'=> $posts]);
    }
    /**
     * creating a new resource.
     *
     * @return \\Illuminate\\Http\\Response
     */
    public function create(Request $request)
    {
        $result = Post::create([
            'text' => $request->task
        ]);
        $post = DB::table('posts')
        ->selectRaw('id,text,complete_flag,DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS create_time')
        ->where('id', $result->id)->get();

        return response( ['post' => $post]);
    }
    /**
     * change the specified resource.
     *
     * @return \\Illuminate\\Http\\Response
     */

    public function checkedChange(Request $request): Response
    {
        $is_checked = $request->is_checked === 'true' ? 1 : 0;
        // Log::debug($is_checked);

        $posts = DB::table('posts')
            ->where('id',$request->id)
            ->update(['complete_flag' => $is_checked]);

        return response($is_checked);
    }

    /**
     * Display a listing of the trash resource.
     *
     * @return \\Illuminate\\Http\\Response
     */
    public function trash()
    {
        $posts =  $this->_getTrashLists();
        return response(view('posts.trash', ['posts' => $posts]));
    }
    /**
     * SoftDelete the specified resource from storage.
     *
     * @param  \\App\\Models\\Post  $post
     * @return \\Illuminate\\Http\\Response
     */
    public function goToTrash(Request $request)
    {
    $result = Post::destroy($request->id);
    return response(['deleted' => $result]);
    }

    /**
     * Back to store the specified resource from storage.
     *
     * @param  \\App\\Models\\Post  $post
     * @return \\Illuminate\\Http\\Response
     */
    public function restore(Request $request)
    {
        $result = Post::where('id',$request->id)->withTrashed()->restore();

        return redirect()->route('post.trash');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @return \\Illuminate\\Http\\Response
     */
    public function delete()
    {
        $result = DB::table('posts')->whereNotNull('deleted_at')->delete();
        $this->_getTrashLists();

        return redirect()->route('post.trash');
    }
    // SQL
    private function _getDisplayLists()
    {
        $result = DB::table('posts')
            ->selectRaw('id,text,complete_flag,DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS create_time')
            ->whereNull('deleted_at')
            ->orderByRaw('created_at DESC')
            ->get();
        return $result;
    }
    private function _getTrashLists()
    {
        $result = DB::table('posts')
        ->selectRaw('id,text,complete_flag,DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS create_time')
        ->whereNotNull('deleted_at')
        ->orderByRaw('created_at DESC')
        ->get();
        return $result;
    }
}
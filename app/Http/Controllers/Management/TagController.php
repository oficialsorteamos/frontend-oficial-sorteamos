<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Department;
use App\Models\Management\Tag\Tag;
use App\Models\Management\Tag\TypeTag;
use App\Models\Management\UserDepartment;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::where('tag_status', 'A')
                    ->get();
        
        return response()->json([
            'tags' => $tags
        ], 200);
    }

    public function fetchTagsType($typeId)
    {
        $tags = Tag::where('tag_status', 'A')
                    ->where('type_tag_id', $typeId)
                    ->get();
        
        return response()->json([
            'tags' => $tags
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            Log::debug('dados etiqueta');
            Log::debug($request);
            $newTag = new Tag();
            $newTag->tag_name = $request->tagData['tag_name'];
            $newTag->type_tag_id = $request->tagData['type_tag']['id'];
            $newTag->tag_description = $request->tagData['tag_description'];
            $newTag->tag_color = $request->tagData['tag_color'];
            
            $newTag->save();

            return response()->json([
                ''
            ], 200);

        } catch (e) {

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $tag = Tag::find($request->tagData['id']);

            $tag->tag_color = $request->tagData['tag_color'];
            $tag->tag_name = $request->tagData['tag_name'];
            $tag->tag_description = $request->tagData['tag_description'];
            $tag->type_tag_id = $request->tagData['type_tag']['id'];
            $tag->save();

            return response()->json([
                [] 
            ], 200);

        } catch (e) {

        }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $tag = Tag::find($id);
            $tag->delete();
        } catch(e) {

        }
    }

    public function fetchTypesTag()
    {
        $typesTag = TypeTag::where('typ_status', 'A')
                            ->orderBy('typ_name')
                            ->get();
        
        Log::debug('tipos de tag');
        Log::debug($typesTag);
        return response()->json([
            'typesTag' => $typesTag 
        ], 200);
    }

    public function fetchTags(Request $params)
    {
        Log::debug('parâmetros tags');
        Log::debug($params);
        $utils = new UtilsController();

        $tags = Tag::with('typeTag')
                    ->whereIn('tag_status', ['A', 'I']);
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $tags = $tags->where('tag_name', 'like', '%'.trim($params['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            $tags = $tags->orWhere('tag_description', 'like', '%'.trim($params['q']).'%');
        }
        $tags = $tags->where('man_tags.type_tag_id', $params['typeTag']);
        $tags = $tags->orderBy('man_tags.tag_status');
        $tags = $tags->orderBy('man_tags.created_at', 'DESC');
        $tags = $tags->get();

        Log::debug('tags cadastradas');
        Log::debug($tags);

        return response()->json([
            'tags'=> $utils->paginateArray($tags->toArray(), $params['perPage'], $params['page']),
            'total'=> count($tags),
        ], 201);
    }
}

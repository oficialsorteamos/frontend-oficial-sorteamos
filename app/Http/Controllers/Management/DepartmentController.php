<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Department;
use App\Models\Management\UserDepartment;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $departments = Department::where('dep_status', 'A')
                                ->get();
        
        return response()->json([
            'departments' => $departments 
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
        try {
            //Salva um novo departamento
            $department = new Department();
            $department->dep_name = $request->departmentData['dep_name'];
            $department->dep_description = $request->departmentData['dep_description'];
            $department->save();

            return response()->json([
                [] 
            ], 200);

        } catch(e) {

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
            $department = Department::find($request->departmentData['id']);
            $department->dep_status = $request->departmentData['dep_status'];
            $department->dep_name = $request->departmentData['dep_name'];
            $department->dep_description = $request->departmentData['dep_description'];
            $department->save();

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
            $department = Department::find($id);
            $department->dep_status = 'I';
            $department->save();
        } catch(e) {

        }
    }

    public function fetchDepartments(Request $params)
    {
        $utils = new UtilsController();

        $departments = Department::whereIn('dep_status', ['A', 'I']);
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $departments = $departments->where('dep_name', 'like', '%'.trim($params['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            $departments = $departments->orWhere('dep_description', 'like', '%'.trim($params['q']).'%');
        }
        $departments = $departments->orderBy('man_departments.dep_status');
        $departments = $departments->orderBy('man_departments.created_at', 'DESC');
        $departments = $departments->get();


        return response()->json([
            'departments'=> $utils->paginateArray($departments->toArray(), $params['perPage'], $params['page']),
            'total'=> count($departments),
        ], 201);
    }

    //Insere um departamento para um usuário
    public function addDepartmentUser($userId, $departmentId)
    {
        $departmentUser = new UserDepartment();
        $departmentUser->user_id = $userId;
        $departmentUser->department_id = $departmentId;
        $departmentUser->save();
    }

    //Atualiza o departamento em que o usuário faz parte
    public function updateUserDepartment($userId, $departments)
    {   
        try {
            //Remove o departamento onde o usuário estava lotado atualmente
            UserDepartment::where('user_id', $userId)
            ->delete();
            
            foreach($departments as $department) {
                Log::debug('departamento');
                Log::debug($department);
                $newUserDepartment = new UserDepartment();
                $newUserDepartment->user_id = $userId;
                $newUserDepartment->department_id = $department['id'];
                $newUserDepartment->save();
            }
            
        } catch(e) {

        }
    }

    //Traz os departamentos associados ao usuário
    public function fetchDepartmentsUser($userId)
    {   
        //Traz todos os departmentos em que o usuário está associado
        $departmentsUser = Department::select('man_departments.id', 'man_departments.dep_name', 'man_departments.dep_description')
                            ->join('man_users_departments', 'man_departments.id', 'man_users_departments.department_id')
                            ->join('users', 'man_users_departments.user_id', 'users.id')
                            ->where('users.id', $userId)
                            ->get();

        return $departmentsUser;
    }

    //Traz um departamento pelo ID
    public function getDepartmentById($departmentId)
    {
        $department = Department::find($departmentId);

        return $department;
    }
}

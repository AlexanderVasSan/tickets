<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ResponseController;
use Kordy\Ticketit\Models\Category;
use Kordy\Ticketit\Models\Priority;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Setting;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\Status;
use Kordy\Ticketit\Models\Comment;
use Carbon\Carbon;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Controllers\TicketsController;
use Validator;
use App\User;
use App\Mail\ActiveTickets;
\Carbon\Carbon::setLocale('es'); 
use DB;
//use Kordy\Ticketit\Models\Ticket;


class TicketController extends ResponseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:ticketit_priorities,id',
            'category_id' => 'required|exists:ticketit_categories,id',
            'user_id' => 'required',
        ],
        [
            'subject.min' => "Asunto mínimo de 3 digitos",
            'subject.required' => "Asunto Requerido",
            'content.min' => "Descripción mínima de 6 digitos",
            'content.required' => "Descripción requerida",
            'priority_id.required' => "Prioridad requerido",
            'category_id.required' => "Categoría requerida",
            'user_id.required' => "Id del usuario requerido"
        ]);

        if($validator->fails()){
            return $this->sendResponse(false, $validator->errors()->first(), null, $validator->errors(), 200);
        }

        $ticket = new Ticket();if($request->incognito == 1)
        {
            $ticket->client = $request->client;
            $ticket->position = $request->marketStall;
    
            $ticket->subject = $request->subject;
    
            $ticket->setPurifiedContent($request->get('content'));
    
            $ticket->priority_id = $request->priority_id;
            $ticket->category_id = $request->category_id;
    
            $ticket->status_id = Setting::grab('default_status_id');
            $ticket->user_id = 1469;
            $ticket->autoSelectAgent();
            $ticket->save();
            session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));
    
            return $this->sendResponse(true, 'Su ticket se ha generado correctamente.', $ticket->toArray(), [], 200);
        }

        $ticket->client = $request->client;
        $ticket->position = $request->marketStall;

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->priority_id = $request->priority_id;
        $ticket->category_id = $request->category_id;

        $ticket->status_id = Setting::grab('default_status_id');
        $ticket->user_id = $request->user_id;
        $ticket->autoSelectAgent();

        $ticket->save();
        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));

        return $this->sendResponse(true, 'Su ticket se ha generado correctamente.', $ticket->toArray(), [], 200);
    }

    public function comment(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required',
            'content'     => 'required|min:6',
            'ticket_id' => 'required',
        ],
        [
            'content.min' => "Descripción mínima de 6 digitos",
            'content.required' => "Descripción requerida",
            'user_id.required' => "Id del usuario requerido",
            'ticket_id.required' => "Id del tickets requerida"
        ]);

        if($validator->fails()){
            return $this->sendResponse(false, $validator->errors()->first(), null, $validator->errors(), 200);
        }

        $comment = new Comment();

        $comment->setPurifiedContent($request->get('content'));

        $comment->ticket_id = $request->get('ticket_id');
        $comment->user_id = $request->user_id;
        $comment->save();

        $ticket = Ticket::find($comment->ticket_id);
        $ticket->updated_at = $comment->created_at;
        $ticket->save();

        return $this->sendResponse(true, 'El comentario se género correctamente.', null, [], 200);
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

    public function open(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
        ]);

        if($validator->fails()){
            return $this->sendResponse(false, 'Validation Error.', null, $validator->errors(), 200);
        }

        $info = Ticket::where('user_id', $request->id)->where('status_id','!=',2)->get();
        return $this->sendResponse(true, 'these are your tickets.', $info->toArray(), [], 200);
    }

    public function close(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
        ]);

        if($validator->fails()){
            return $this->sendResponse(false, 'Validation Error.', null, $validator->errors(), 200);
        }
        $info = Ticket::where('user_id', $request->id)->where('status_id',2)->get();
        return $this->sendResponse(true, 'these are your tickets.', $info->toArray(), [], 200);
    }

    public function info(Request $request)
    {

        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
        ]);


        if($validator->fails()){
            return $this->sendResponse(false, 'Validation Error.', null, $validator->errors(), 200);
        }

        $registro = Ticket::where('id' , $request->id)->get();
        $name = User::select('name')->where('id', $registro[0]->user_id )->get();
        $prioriti = Priority::select('name')->where('id', $registro[0]->priority_id )->get();
        $categori = Category::select('name')->where('id', $registro[0]->category_id )->get();
        $status = Status::select('name')->where('id', $registro[0]->status_id )->get();
        $owner = User::select('name')->where('id', $registro[0]->agent_id )->get();
        $update = Ticket::select('updated_at')->where('id', $request->id )->get();
        $create = Ticket::select('created_at')->where('id', $request->id )->get();
        $actualizado = Carbon::parse($update[0]->updated_at)->diffForHumans();
        $creado = Carbon::parse($create[0]->created_at)->diffForHumans();
        $coments = Comment::where('ticket_id',$request->id)->get();
        $information = ['ticket' =>$registro,
                        'owner' => $name[0]->name,
                        'status' => $status[0]->name,
                        'priority' => $prioriti[0]->name,
                        'agent' => $owner[0]->name,
                        'category' => $categori[0]->name,
                        'created' => $creado,
                        'updated' => $actualizado,
                        'comments' => $coments];
        return $this->sendResponse(true, 'this is your ticket information.', $information, [], 200);
    }

    public function closeticket(Request $request)
    {

        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
            'subject' => 'required',
            'content' => 'required',
            'priority_id' => 'required',
            'category_id' => 'required',
            'status_id' => 'required',
            'agent_id' => 'required',
        ]);


        if($validator->fails()){
            return $this->sendResponse(false, 'Validation Error.', null, $validator->errors(), 200);
        }
        $ticket->id = $request->id;

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->status_id = $request->status_id;
        $ticket->category_id = $request->category_id;
        $ticket->priority_id = $request->priority_id;

        if ($request->input('agent_id') == 'auto') {
            $ticket->autoSelectAgent();
        } else {
            $ticket->agent_id = $request->input('agent_id');
        }
        if (($ticket->status_id  == 2) && ($ticket->category_id >= 8 && $ticket->category_id <= 11 )) {
        $response = $this->client->request('GET', 'http://54.163.246.228/seguridad/WS/ws_eliminar_encuesta_user.php?usuario='.$ticket->agent_id.'&categoria='.$ticket->category_id);
        }

        $ticket->save();
        
        return $this->sendResponse(true, 'ticket cerrado correctamente.', $information, [], 200);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sendMail()
    {
        try {

            \Mail::to(['mgarcia@cassesa.com.gt', 'lsaravia@cassesa.com.gt', 'cdiaz@cassesa.com.gt', 'cmendez@researchmobile.co', 'prosales@researchmobile.co'])->send(new ActiveTickets());
            //\Mail::to(['prosales@researchmobile.co'])->send(new ActiveTickets());

            echo 'Enviado';
        }
        catch(\Exception $e) { echo $e->getMessage(); }
    }

    public function options2(Request $request)
    {
        $ticketController = new TicketsController(new Ticket(), new Agent());
        $indicator_period = 2;
        $monthly_performance = $ticketController->monthlyPerfomance($indicator_period);
        $datos = [];
        $implodes = array();
        foreach($monthly_performance['categories'] as $month => $records)
        {
            $month = explode(' :: ', $records);
            if (count($month) == 1 ) {
                $implode = implode(", ", $month);
                array_push($implodes, $implode);
            } 
        }

        foreach($monthly_performance['interval'] as $month => $records)
        {
            $month = array(
                2,
                30,
                50,
                10
            );
            $month1 = array(
                10,
                112,
                0,
                1
            );
            $month2 = array(
                0,
                0,
                0,
                0
            );
            if($month > 0)
            {
                $monthly_performance=array(
                    'categories'=> $implodes,
                    'interval'=> [
                        "June 2021" => $month,
                        "July 2021" => $month1,
                        "August 2021" => $month2
                    ]
                );
                
            }
        }

        $categories_all = Category::orderBy('name', 'asc')->get();
        $categories_share = [];
        foreach ($categories_all as $cat) {
            //$categories_share[$cat->name] = $cat->tickets()->count();
            if($cat->name == "Electrónica"){
                $categories_share[$cat->name] = 10;
            }elseif($cat->name == "Logística"){
                $categories_share[$cat->name] = 112;
            }elseif($cat->name == "Recursos Humanos"){
                $categories_share[$cat->name] = 0;
            }elseif($cat->name == "Monitoreo"){
                $categories_share[$cat->name] = 1;
            }
        }
        $completos = Ticket::complete()->count();
        return $this->sendResponse(true, 'There is all options.', $completos, [], 200);
    }
    public function options(Request $request)
    {
        $datos = array();
        $categories = Category::orderBy('name', 'asc')->get();
        $priorities = Priority::all();
        foreach ($categories as $keyC => $valC) {
            $el = explode(" :: ", $valC['name']);
            if (count($el) == 1 ) {
                $collect = array();
                foreach ($categories as $keyC1 => $valC1) {
                    $al = explode(" :: ", $valC1['name']);
                    if (count($al) > 1) {
                        if ($al[0] == $el[0]) {
                            $inf = substr($al[1], 0);
                            array_push($collect, [
                                'id' => $valC1['id'],
                                'name' => $inf,
                                'color' => $valC1['color']
                            ]);
                        }
                    }  
                }
                $respond = array(
                    'name' => $el[0],
                    'subcategories' => $collect
                );
                array_push($datos, $respond);
            }             
        }
        $options = ['categories' => $datos,
                    'priorities' => $priorities];

        return $this->sendResponse(true, 'There is all options.', $options, [], 200);
    }
}

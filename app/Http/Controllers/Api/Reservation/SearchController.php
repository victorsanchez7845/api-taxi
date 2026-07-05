<?php

namespace App\Http\Controllers\Api\Reservation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Reservation\SearchRepository;
use App\Traits\MailjetTrait;
use App\Traits\FunctionsTrait;
use App\Models\ReservationsFollowUp;
use App\Models\DestinationMail;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class SearchController extends Controller
{
    use MailjetTrait, FunctionsTrait;

    public function index(Request $request, SearchRepository $search)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'nullable|string',
            'code' => 'max:25|required_without:uuid',
            'email' => 'email|max:75|required_without:uuid',
            'language' => 'required|in:en,es',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all()
                ]
            ], 404);
        }

        $search->setData($request);
        $data = $search->search();

        if ($data == false) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Reservation not found'
                ]
            ], 404);
        }

        return response()->json($data, 200);
    }

    public function send(Request $request, SearchRepository $search)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:12',
            'email' => 'required|email|max:75',
            'language' => 'required|in:en,es',
            'type' => 'required|in:new,update,cancel,confirmed',
            'provider' => 'nullable|in:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all()
                ]
            ], 404);
        }

        $search->setData($request);
        $data = $search->search();

        if ($data == false) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Reservation not found'
                ]
            ], 404);
        }

        if ($data['site']['email'] == 0) {
            return response()->json([
                'error' => [
                    'code' => 'mailing_disabled',
                    'message' => 'Mailing disabled'
                ]
            ], 404);
        }

        $provider = $this->getProvider($data['config']['destination_id']);
        $template = $this->getTemplate($request, $data);

        if ($template['status'] == false) {
            return response()->json($template['data'], 404);
        }

        if ($request->language == "en") {
            switch ($request->type) {
                case 'new':
                    $subject = '🎟 Thank you for booking with us | ' . $data['site']['name'];
                    break;
                case 'update':
                    $subject = '🎟 Your reservation data updated | ' . $data['site']['name'];
                    break;
                case 'confirmed':
                    $subject = '🎟 Your reservation has been confirmed | ' . $data['site']['name'];
                    break;
                case 'cancel':
                    $subject = '🎟 Reservation cancelled | ' . $data['site']['name'];
                    break;
                default:
                    $subject = '🎟 Reservation | ' . $data['site']['name'];
                    break;
            }
        } else {
            switch ($request->type) {
                case 'new':
                    $subject = '🎟 Gracias por reservar con nosotros | ' . $data['site']['name'];
                    break;
                case 'update':
                    $subject = '🎟 Datos de reservación actualizados | ' . $data['site']['name'];
                    break;
                case 'confirmed':
                    $subject = '🎟 Tu reservación ha sido confirmada | ' . $data['site']['name'];
                    break;
                case 'cancel':
                    $subject = '🎟 Reservación cancelada | ' . $data['site']['name'];
                    break;
                default:
                    $subject = '🎟 Reservación | ' . $data['site']['name'];
                    break;
            }
        }

        $provider_email = [];

        if (
            isset($provider->id) &&
            !empty($provider->transactional_emails) &&
            isset($request->provider)
        ) {
            $emails = explode(",", trim($provider->transactional_emails));

            foreach ($emails as $value) {
                $provider_email[] = [
                    "Email" => trim($value),
                    "Name" => $provider->name,
                ];
            }
        }

        $email_data = [
            "Messages" => [
                [
                    "From" => [
                        "Email" => $data['site']['email'],
                        "Name" => $data['site']['name'] ?? "Bookings",
                    ],
                    "To" => [
                        [
                            "Email" => $request->email,
                            "Name" => $data['client']['first_name'],
                        ]
                    ],
                    "Bcc" => $provider_email,
                    "Subject" => $subject,
                    "TextPart" => "Dear client",
                    "HTMLPart" => $template['data']
                ]
            ]
        ];

        $email_response = $this->sendMailjet($email_data);

        if (
            isset($email_response['Messages'][0]['Status']) &&
            $email_response['Messages'][0]['Status'] == "success"
        ) {
            $follow_up_db = new ReservationsFollowUp;
            $follow_up_db->name = 'Sistema';
            $follow_up_db->text = 'E-mail enviado (' . $request->type . ')';
            $follow_up_db->type = 'INTERN';
            $follow_up_db->reservation_id = $data['config']['id'];
            $follow_up_db->save();

            return response()->json([
                'status' => "success",
                'mailjet_response' => $email_response
            ], 200);
        }

        $follow_up_db = new ReservationsFollowUp;
        $follow_up_db->name = 'Sistema';
        $follow_up_db->text = 'No fue posible enviar el e-mail del cliente, por favor contactar a Desarrollo';
        $follow_up_db->type = 'INTERN';
        $follow_up_db->reservation_id = $data['config']['id'];
        $follow_up_db->save();

        return response()->json([
            'error' => [
                'code' => 'mailing_system',
                'message' => 'The mailing platform has a problem, please report to development'
            ],
            'mailjet_response' => $email_response
        ], 404);
    }

    public function makeQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'max:250',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all()
                ]
            ], 404);
        }

        $qr = QrCode::create($request->code);
        $writer = new PngWriter;
        $result = $writer->write($qr);

        header("Content-Type: " . $result->getMimeType());
        echo $result->getString();
        exit;
    }

    public function getTemplate(Request $request, $data)
    {
        App::setLocale($request->language);

        $mail = DestinationMail::where(
            'destination_id',
            $data['config']['destination_id']
        )->get();

        $creation_date = $this->getPrettyDate(
            $data['config']['creation_date'],
            $request->language
        );

        $html = view('mailing.transportation', [
            'data' => $data,
            'mail' => $mail,
            'creation_date' => $creation_date
        ])->render();

        return [
            'status' => true,
            'data' => $html
        ];
    }

    public function getProvider($id)
    {
        $data = DB::select(
            'SELECT id, name, transactional_phone, transactional_emails, is_default 
             FROM providers 
             WHERE destination_id = :id 
             AND is_default = 1',
            ['id' => $id]
        );

        if (isset($data[0])) {
            return $data[0];
        }

        return [];
    }

    public function getTypesCancellations(Request $request, SearchRepository $search)
    {
        return $search->getTypesCancellations($request);
    }
}

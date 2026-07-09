@php    
    $lang = app()->getLocale();
    if( $data['config']['is_cancelled'] == 1 ):
        $data['status'] = "CANCELLED";
    endif;

    $reservation_status_label = $data['status'];
    switch ($lang) {
        case 'es':
                if($reservation_status_label == "CANCELLED"):
                    $reservation_status_label = "CANCELADO";
                elseif($reservation_status_label == "CONFIRMED"):
                    $reservation_status_label = "CONFIRMADO";
                else:
                    $reservation_status_label = "PENDIENTE";
                endif;
            break;        
        default:
            break;
    }
    $provider_name = $data['provider']['name'];
    $destination_name = $data['provider']['destination'];
@endphp
<!DOCTYPE html>
<html lang="{{$lang}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bookings</title>
    <style>
        body{
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;            
            font-size: 11pt;
        }
        p{
            font-size: 11pt;
            line-height: 1.5;
            margin: 0px;
        }
        .gray_color{
            color: #6A829E;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 5px;
            margin-top: 15px;
        }

        table.table_init {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 15px;
        }
        .header{
            text-align: center;
        }
        div.orange_content{
            border-radius: 15px 15px 0px 0px;
            background-color: {{$data['site']['color']}};
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        div.orange_content table{
            width: 100%;
        }
        div.orange_content table td{
            text-align:left; 
            vertical-align: top; 
            padding: 25px;
        }
        div.orange_content table td h1{
            font-size: 22pt;
            margin: 0px;
            color: white;
            margin-bottom: 8px;
        }
        div.orange_content table td p{
            font-size: 11pt;
            color: white;
            margin: 0px;
        }
        div.orange_content table td p.name{
            font-size: 16pt;
            font-weight: bold;
            color: white;
            margin: 0px;
            margin-bottom: 15px;
        }

        td.white_content{
            background-color: white;
            padding: 25px;
        }
        td.white_content.information > p{
            margin-bottom: 8px;
        }
        p.label{
            font-weight: bold;
            margin-bottom: 8px;
        }
        hr{
            border: 0px;
            border-top: 1px solid #CCD5D8;
            margin: 0px;
        }
        table.destinations_table{
            width: 100%;
            border-collapse: collapse;
        }
        table.destinations_table td{
            width: 50%;
            padding-bottom: 10px;
        }  
        a.pink{
            color: #FF3366;
            text-decoration: none;
        }
        td.important_information:empty{
            padding: 0px !important;
        }
        .important_information p{
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .important_information hr{
            margin-top: 15px;
            margin-bottom: 15px;
        }
        span.payment{
            background-color: #191970;
            color: white;
            padding: 15px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: bold;
        }
        span.payment.type-CONFIRMED,
        span.type-CONFIRMED{
            background-color: #198f51;
            color: white;
        }
        span.payment.type-CANCELLED,
        span.type-CANCELLED{
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body style="background-color: #f7fafb;">
    <div class="container">
        <div class="header">
            <img src="{{ $data['site']['logo'] }}" style="max-width:600px;">
        </div>
        <table class="table_init">
            <tbody>                
                <tr>
                    <td>
                        <div class="orange_content">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="text-align:center;">
                                            <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/top-vehicle.png?updatedAt=1693244044317">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top:0px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td style="padding:0px;">
                                                            <h1>{{ __('mailing/client.hello') }}</h1>
                                                            <p class="name">{{$data['client']['first_name']}} {{$data['client']['last_name']}}</p>
                                                        </td>
                                                        <td style="text-align:right;padding:0px;">
                                                            <h4 style="margin:0px;color:white;margin-bottom:8px;">{{ __('mailing/client.reservation_status') }}</h4>
                                                            <span class="payment type-{{$data['status']}}">{{ $reservation_status_label }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @if($lang == "en")
                                                <p>Thank you very much for booking with us, your service will be operated by {{ $provider_name }} which is our official tourist transportation company in {{ $destination_name }}.</p>
                                            @else
                                                <p>Muchas gracias por reservar con nosotros, Su servicio sera operado por {{ $provider_name }} la cuál es nuestra empresa de transporte turístico oficial en {{ $destination_name }}.</p>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content">
                        <p class="gray_color" style="margin-bottom:15px;">
                            @if($lang == "en")
                                This email shows in detail the information of your reservation made on {{ $creation_date }}, in which we ask that if it is not correct please contact us to make the corresponding modifications.
                            @else
                                En el presente correo se muestra a detalle la información de su reservación realizada el día {{ $creation_date }}, en el cual le pedimos que si no es correcta ponganse en contacto con nosotros para hacer las modificaciones correspondientes.
                            @endif
                        </p>
                        <p style="margin-bottom:15px;">
                            <strong>
                                @if($lang == "en")
                                    PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE {{ strtoupper($provider_name) }} REPRESENTATIVE TO BOARD YOUR UNIT.
                                @else
                                    POR FAVOR, PRESENTE ESTE RECIBO IMPRESO O DIGITAL (CELULAR) AL REPRESENTANTE DE {{ strtoupper($provider_name) }}, PARA ABORDAR SU UNIDAD.
                                @endif
                            </strong>
                        </p>
                        <p style="margin-bottom:15px;">
                            <strong>
                                @if($lang == "en")
                                    AT {{ strtoupper($provider_name) }} WE TAKE YOUR SAFETY VERY SERIOUSLY. THEREFORE, IT WILL BE NECESSARY TO PRESENT AN OFFICIAL ID AND SIGN THE SERVICE PICK UP FORM AT THE TIME OF BOARDING YOUR UNIT.
                                @else
                                    EN {{ strtoupper($provider_name) }} NOS TOMAMOS MUY EN SERIO SU SEGURIDAD. POR ELLO, SERÁ NECESARIO PRESENTAR UNA IDENTIFICACIÓN OFICIAL Y FIRMAR EL FORMULARIO DE TOMA DE SERVICIO AL MOMENTO DE ABORDAR SU UNIDAD.
                                @endif
                            </strong>
                        </p>
                        <h2>Total: {{ number_format($data['sales']['total'],2) }} {{ $data['config']['currency'] }}</h2>
                        @if(sizeof($data['items']) >= 1)
                            @foreach ($data['items'] as $key => $value)     
                                <div style="background-color:#DDE9FA;padding: 15px;margin-bottom:15px;">
                                    <table style="width:100%;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.booking_id') }}</p>
                                                    <p style="font-size: 14pt;">{{$key}}</p>
                                                </td>
                                                <td rowspan="7" style="text-align:right;">
                                                    @php
                                                        $QR = urlencode('https://api.taxidominicana.com/api/v1/mailing/reservation/view?code='.$key.'&email='.trim(strtolower($data['client']['email'])).'&language='.$lang);
                                                    @endphp
                                                    <img src="{{config('app.url')}}/api/v1/reservation/qr?code={{$QR}}" width="250">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.type') }}</p>
                                                    <p>{{ (($value['is_round_trip'] == 0)? __('mailing/client.one_way') : __('mailing/client.round_trip') ) }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.name') }}</p>
                                                    <p>{{$data['client']['first_name']}} {{$data['client']['last_name']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.phone') }}</p>
                                                    <p>{{$data['client']['phone']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">E-mail</p>
                                                    <p>{{$data['client']['email']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">Website</p>
                                                    <p>{{$data['site']['name']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.payment_status') }}</p>
                                                    <p>
                                                        @if( $data['payments']['total'] >= $data['sales']['total'] )
                                                            {{ __('mailing/client.paid') }}
                                                        @else
                                                            {{ __('mailing/client.pendiente') }}
                                                        @endif
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-top:10px;"><hr></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table class="destinations_table">
                                                        <tbody>
                                                            @php
                                                                $itemCount = 0;
                                                            @endphp
                                                            <tr>
                                                                <td style="vertical-align:baseline;">
                                                                    <p class="label">{{ __('mailing/client.from') }}</p>
                                                                    <p>{{ $value['from']['name'] }}</p>
                                                                </td>
                                                                <td style="vertical-align:baseline;">
                                                                    <p class="label">{{ __('mailing/client.to') }}</p>
                                                                    <p>{{ $value['to']['name'] }}</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.pickup') }}</p>
                                                                    <p>{{ $value['pickup'] }}</p>
                                                                    @if(!empty($value['departure_pickup']))
                                                                        <p class="label" style="margin-top:8px;">{{ __('mailing/client.departure_pickup') }}</p>
                                                                        <p>{{ $value['departure_pickup'] }}</p>
                                                                    @endif
                                                                </td>
                                                                <td style="vertical-align: baseline;">
                                                                    <p class="label">{{ __('mailing/client.passengers') }}</p>
                                                                    <p>{{ $value['passengers'] }}</p>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.service_type') }}</p>
                                                                    <p>{{ $value['service_type_name'] }}</p>                                                                    
                                                                </td>
                                                                @if(!empty($value['flight_number']))
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.flight_number') }}</p>
                                                                    <p>{{ $value['flight_number'] }}</p>                                                                    
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>                                
                                </div>
                            @endforeach
                        @endif

                        <div>
                            <p style="margin: 15px 0px 5px 0px; font-size:14pt;"><strong>{{ __('mailing/client.indications') }}</strong></p>
                            @if($lang == "en")
                                <p style="margin-bottom: 8px;">In this email you will find a summary of your reservation information, it is important that you can validate that the information is correct, and in case of any change in the information of your flight, doubts or clarifications contact us so we can assist you in the best possible way.</p>
                                <p>If you are at the airport or at your hotel and do not see us, call us at <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> or send us a WhatsApp to <a class="pink" href="https://api.whatsapp.com/send?phone=5219982127069&text=Hello!">+52 (998) 212 7069</a>.</p>
                            @else
                                <p style="margin-bottom: 8px;">En este correo electrónico encontrarás un resumen de la información de tu reservación, es importante que puedas validar que la información es correcta, y en caso de algún cambio en la información de tu vuelo, dudas o aclaraciones contáctanos para poder atenderte de la mejor manera posible.</p>
                                <p>Si estás en el Aeropuerto o en tu Hotel y no nos ves, llámanos al <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> o envíanos un WhatsApp al <a class="pink" href="https://api.whatsapp.com/send?phone=5219982127069&text=%C2%A1Hola!">+52 (998) 212 7069</a>.</p>
                            @endif                            
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content" style="border-top: 1px solid #CCD5D8; text-align:center;">
                        @if($lang == "en")                            
                            <h3 style="margin-bottom: 0px; color: #191970;">Thank you for your reservation!</h3>
                        @else                            
                            <h4 style="margin-bottom: 0px; color: #191970;">¡Gracias por tu reservación!</h4>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="https://www.tripadvisor.com/Attraction_Review-g150807-d25085358-Reviews-Caribbean_Transfers-Cancun_Yucatan_Peninsula.html" target="_blank">
                            <img src="https://ik.imagekit.io/x4uujhiqht/taxi-dominicana.png" style="width:600px;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="white_content important_information" style="padding-top: 0px;">
                        @if( $mail->isNotEmpty() )
                            @foreach($mail as $key => $value)
                                @php
                                    print_r($value->text);
                                @endphp
                            @endforeach
                        @endif                                                
                    </td>
                </tr>
                <tr>
                    <td class="white_content information" style="text-align:left; padding-top: 0px;">
                        @if($lang == "en")                            
                        <h4 style="color:red; text-align:center;">IT IS VERY IMPORTANT FOR YOU TO KNOW</h4>
                    
                        <h3>For ARRIVALS</h3>
                    
                        <p><strong>Upon arrival at Punta Cana International Airport (PUJ), please follow these instructions to ensure an easy and fast connection with your transportation service:</strong></p>
                    
                        <p>1.- Please download WhatsApp before your trip and keep us informed from the moment your flight lands at Punta Cana Airport. This will help our support team and airport staff assist you faster.</p>
                    
                        <p>2.- As soon as you arrive and have internet connection, please send us a selfie or recent photo of yourself through WhatsApp so our airport staff can identify you more easily at the meeting point.</p>
                    
                        <p>3.- When you arrive at the airport, you will first go through Immigration. After that, you will be directed to the baggage claim area.</p>
                    
                        <p>4.- Once you have picked up your luggage, you will be directed to Customs.</p>
                    
                        <p>5.- After clearing Customs, please proceed to walk <strong>OUTSIDE</strong> your arrival terminal. <strong>It is very important to go all the way outside, as there may be people offering tourist information, tours, transportation, or vacation packages. These people are not part of our service. For your own convenience, please do not stop anywhere between customs and the airport exit.</strong></p>
                    
                        <p>6.- One of our representatives may be waiting for you at the designated meeting point. The representative will have a board with the {{ $provider_name }} LOGO and will assist you in locating your assigned transportation.</p>
                    
                        <p>7.- In some cases, depending on airport operations or staff availability at that moment, our support team may guide you directly by WhatsApp or phone so you can board your transportation correctly.</p>
                    
                        <p>8.- <strong>Meeting point by terminal:</strong></p>
                    
                        <p><strong>Terminal A:</strong> Our representative is normally located outside the terminal, in front of the <strong>Welcome Punta Cana</strong> area.</p>
                    
                        <p><strong>Terminal B:</strong> Due to remodeling and airport operations, the meeting point may vary depending on the day and time. It may be at <strong>Counter 8</strong> or outside the terminal exit. Our team will guide you to the correct meeting point based on your arrival time.</p>
                    
                        <p>9.- If you cannot find our representative or need assistance, please contact our customer support number immediately.</p>
                    
                        <p><strong>Customer Support / WhatsApp:</strong> <a class="pink" href="https://api.whatsapp.com/send?phone=529987322416&text=Hello!">+52 998 732 2416</a></p>
                    
                        <p>10.- Tips for driver are <strong>NOT INCLUDED</strong> and are completely optional.</p>
                    
                        <p>IMPORTANT: <strong>DO NOT</strong> be fooled by other people at the airport. Some may say they work with us, that they know your reservation, or that your transportation is not available. Our official representatives will identify themselves with the {{ $provider_name }} LOGO. For your own convenience, please only follow the instructions provided in your confirmation email and by our official support team.</p>
                    
                        <p><strong>REMEMBER THAT WE WILL BE MONITORING YOUR ARRIVAL FLIGHT IF IT IS DELAYED OR ARRIVES EARLY.</strong> It is not necessary to contact us if your arrival flight is delayed or arrives early, as our team will be aware of the flight status. Only if your flight is canceled, changed, or you are assigned a different flight number, please contact us as soon as possible with the updated flight information so we can reschedule your transportation.</p>
                    
                        <h3>For DEPARTURES</h3>
                    
                        <p>For departure services, please note that <strong>we do not monitor departure flights</strong>.</p>
                    
                        <p>Your pick-up time was scheduled based on the flight information provided at the time of booking, estimated travel time from your hotel to Punta Cana International Airport, day of the week, and possible traffic conditions.</p>
                    
                        <p>If you need to update your departure pick-up time, the request must be made at least <strong>24 hours in advance</strong>. Same-day schedule changes are subject to availability and may have an additional cost.</p>
                    
                        <p>Please be ready <strong>outside the hotel lobby or at the confirmed pick-up point</strong> at the scheduled time. Drivers are not always able to call passengers upon arrival, so it is very important that you are already waiting at the agreed location.</p>
                    
                        <p>The tolerance time is <strong>10 minutes</strong>. If you are not at the pick-up point within this time, the service will be considered a <strong>no-show</strong>.</p>
                    
                        <p>No-show services are <strong>non-refundable and cannot be rescheduled</strong>.</p>
                    
                        <h4>Policies</h4>                            
                        <p class="gray_color">In case the service has been paid by credit card, you may be required to present the card used for payment and a valid identification when boarding.</p>
                    
                        <p class="gray_color">Cancellations must be requested at least 24 hours before your scheduled arrival or departure service. If your fare includes flexible or Plus conditions, you may be eligible for a partial refund according to the conditions of your reservation. Otherwise, the service may be non-refundable.</p>
                    
                        <h5>Service Hours</h5> 
                        <p class="gray_color">If you need to change the time of your service, please contact us at least 24 hours before the scheduled pick-up time. Same-day changes are subject to availability and may have an additional cost. Contact us from 7AM to 11PM from Monday to Sunday at <a class="pink" href="tel:+529987322416">+52 998 732 2416</a> or email <a class="pink" href="mailto:booking@taxidominicana.com">booking@taxidominicana.com</a></p>
                    @else
                        <h4 style="color:red; text-align:center;">ES MUY IMPORTANTE QUE SEPAS</h4>
                    
                        <h3>Para LLEGADAS</h3>
                    
                        <p><strong>A su llegada al Aeropuerto Internacional de Punta Cana (PUJ), siga estas instrucciones para garantizar una conexión fácil y rápida con su servicio de transporte:</strong></p>
                    
                        <p>1.- Por favor descargue WhatsApp antes de su viaje y manténganos informados desde el momento en que su vuelo aterrice en el Aeropuerto de Punta Cana. Esto ayudará a nuestro equipo de soporte y al staff de aeropuerto a asistirle más rápido.</p>
                    
                        <p>2.- Tan pronto como llegue y tenga conexión a internet, por favor envíenos por WhatsApp una selfie o una foto reciente suya para que nuestro staff de aeropuerto pueda identificarle más fácilmente en el punto de encuentro.</p>
                    
                        <p>3.- Cuando llegue al aeropuerto, primero pasará por el control de inmigración. Después será dirigido al área de recogida de equipaje.</p>
                    
                        <p>4.- Una vez que haya recogido su equipaje, será dirigido a Aduana.</p>
                    
                        <p>5.- Después de pasar Aduana, diríjase al <strong>EXTERIOR</strong> de su terminal de llegada. <strong>Es muy importante salir completamente de la terminal, ya que puede haber personas ofreciendo información turística, tours, transporte o paquetes vacacionales. Estas personas no forman parte de nuestro servicio. Por su propia comodidad, no se detenga entre Aduana y la salida del aeropuerto.</strong></p>
                    
                        <p>6.- Uno de nuestros representantes puede estar esperándole en el punto de encuentro designado. El representante tendrá un letrero con el LOGO de {{ $provider_name }} y le ayudará a localizar su transporte asignado.</p>
                    
                        <p>7.- En algunos casos, dependiendo de la operación del aeropuerto o de la disponibilidad del staff en ese momento, nuestro equipo de soporte podrá guiarle directamente por WhatsApp o llamada para que pueda abordar correctamente su transporte.</p>
                    
                        <p>8.- <strong>Punto de encuentro por terminal:</strong></p>
                    
                        <p><strong>Terminal A:</strong> Nuestro representante normalmente se encuentra fuera de la terminal, frente al área de <strong>Welcome Punta Cana</strong>.</p>
                    
                        <p><strong>Terminal B:</strong> Debido a remodelaciones y operación del aeropuerto, el punto de encuentro puede variar dependiendo del día y la hora. Puede ser en el <strong>Counter 8</strong> o fuera de la salida de la terminal. Nuestro equipo le indicará el punto correcto de acuerdo con su horario de llegada.</p>
                    
                        <p>9.- Si no puede encontrar a nuestro representante o necesita asistencia, por favor contacte inmediatamente a nuestro número de soporte al cliente.</p>
                    
                        <p><strong>Soporte al cliente / WhatsApp:</strong> <a class="pink" href="https://api.whatsapp.com/send?phone=529987322416&text=%C2%A1Hola!">+52 998 732 2416</a></p>
                    
                        <p>10.- La propina para el conductor <strong>NO ESTÁ INCLUIDA</strong> y es completamente opcional.</p>
                    
                        <p>IMPORTANTE: <strong>NO</strong> se deje engañar por otras personas en el aeropuerto. Algunas personas pueden decir que trabajan con nosotros, que conocen su reservación o que su transporte no está disponible. Nuestros representantes oficiales se identificarán con el LOGO de {{ $provider_name }}. Por su propia comodidad, siga únicamente las instrucciones proporcionadas en su correo de confirmación y por nuestro equipo oficial de soporte.</p>
                    
                        <p><strong>RECUERDE QUE MONITOREAMOS SU VUELO DE LLEGADA SI SE RETRASA O LLEGA ANTES DE LO PREVISTO.</strong> No es necesario contactarnos si su vuelo de llegada se retrasa o llega antes, ya que nuestro equipo estará al tanto del estado del vuelo. Únicamente si su vuelo es cancelado, cambiado o se le asigna un número de vuelo diferente, por favor contáctenos lo antes posible con la información actualizada para poder reprogramar su transporte.</p>
                    
                        <h3>Para SALIDAS</h3>
                    
                        <p>Para servicios de salida, por favor tenga en cuenta que <strong>no monitoreamos vuelos de salida</strong>.</p>
                    
                        <p>Su hora de recogida fue programada con base en la información de vuelo proporcionada al momento de reservar, el tiempo estimado de traslado desde su hotel hacia el Aeropuerto Internacional de Punta Cana, el día de la semana y posibles condiciones de tráfico.</p>
                    
                        <p>Si necesita actualizar la hora de recogida de su salida, la solicitud debe realizarse con al menos <strong>24 horas de anticipación</strong>. Los cambios de horario el mismo día están sujetos a disponibilidad y pueden tener un costo adicional.</p>
                    
                        <p>Por favor esté listo <strong>fuera del lobby del hotel o en el punto de recogida confirmado</strong> a la hora programada. Los conductores no siempre tienen posibilidad de llamar a los pasajeros al llegar, por lo que es muy importante que ya se encuentre esperando en el lugar acordado.</p>
                    
                        <p>El tiempo de tolerancia es de <strong>10 minutos</strong>. Si no se encuentra en el punto de recogida dentro de este tiempo, el servicio será considerado como <strong>no-show</strong>.</p>
                    
                        <p>Los servicios marcados como no-show <strong>no son reembolsables y no pueden ser reagendados</strong>.</p>
                    
                        <h4>Políticas</h4>
                    
                        <p class="gray_color">En caso de que el servicio haya sido pagado con tarjeta, es posible que deba presentar la tarjeta utilizada para el pago y una identificación válida al abordar.</p>                            
                    
                        <p class="gray_color">Las cancelaciones deben solicitarse al menos 24 horas antes de su servicio programado de llegada o salida. Si su tarifa incluye condiciones flexibles o Plus, podría ser elegible para un reembolso parcial de acuerdo con las condiciones de su reservación. De lo contrario, el servicio puede no ser reembolsable.</p>
                    
                        <h5>Horario de Servicio</h5> 
                        <p class="gray_color">Si necesita cambiar la hora de su servicio, por favor contáctenos al menos 24 horas antes de la hora programada de recogida. Los cambios el mismo día están sujetos a disponibilidad y pueden tener un costo adicional. Contáctenos de 7AM a 11PM de Lunes a Domingo al número <a class="pink" href="tel:+529987322416">+52 998 732 2416</a> o al correo <a class="pink" href="mailto:booking@taxidominicana.com">booking@taxidominicana.com</a></p>
                    @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px; text-align:center;">
                        <div>
                            <a href="https://www.facebook.com/profile.php?id=61591239112562" target="_blank"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/facebook.png?updatedAt=1692978703979" style="margin-right: 15px;"></a>
                            <!--<a href="#" target="_blank"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/instagram.png?updatedAt=1692978703965"></a>-->
                        </div>
                        <p style="font-size: 11pt; color: #6A829E;">&copy; {{ $provider_name }} | {{ __('mailing/client.rights_reserved') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>

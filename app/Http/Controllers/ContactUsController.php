<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\ContactUs;


class ContactUsController extends Controller
{
    public function SendMessage(Request $request){
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'message' => 'required'
            ]);

            if ($validator->fails()) {
                $response = APIHelpers::createApiResponse(true , 406 ,  'بعض الحقول مفقودة', 'بعض الحقول مفقودة' , null, $request->lang );
                return response()->json($response , 406);
            }

        $contact = new ContactUs;
        $contact->phone = $request->phone;
        $contact->message = $request->message;
        $contact->save();
        $response = APIHelpers::createApiResponse(false , 200 ,  '', '' , $contact, $request->lang );
        return response()->json($response , 200);
    }
}

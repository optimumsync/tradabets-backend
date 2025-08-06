<?php
namespace App\Http\Controllers\API\V1;
use App\Balance;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\User;
use Illuminate\Http\Request;
class UserApiController extends Controller
{
    public function getToken(Request $request)
    {
        // convert into an object
        $this->data = $request->getContent();
        $this->data = str_replace('&', '&amp;', $this->data);
        $this->data = new \SimpleXMLElement($this->data, LIBXML_PARSEHUGE);
        $user = User::find($this->data->Token->playerGuid);
        $result = array();
        $result['Token'] = ['token'=>$user->token,'playerGuid'=>$user->id];
        $response = $this->convert_response($result);
        return response($response, 200)->header('Content-Type', 'application/xml');
    }
    public function convert_response($response)
    {
        $array = json_decode(json_encode($response), True);
        function array2xml($data, $name = 'Response', &$doc = null, &$node = null)
        {
//            echo "<pre>";
//            print_r($data);
//            exit;
            if ($doc == null) {
                $doc = new \DOMDocument('1.0', 'UTF-8');
                $doc->formatOutput = TRUE;
                $node = $doc;
            }
            if (is_array($data)) {
                foreach ($data as $var => $val) {
                    if (is_numeric($var)) {
                        array2xml($val, $name, $doc, $node);
                    } else {
                        if (!isset($child)) {
                            $child = $doc->createElement($name);
                            $node->appendChild($child);
                        }
                        array2xml($val, $var, $doc, $child);
                    }
                }
            } else {
                $child = $doc->createElement($name);
                $node->appendChild($child);
                $textNode = $doc->createTextNode($data);
                $child->appendChild($textNode);
            }
            if ($doc == $node) return $doc->saveXML();
        }
        return array2xml($array);
    }
    public function getBalance(Request $request)
    {
        // convert into an object
        $this->data = $request->getContent();
        $this->data = str_replace('&', '&amp;', $this->data);
        $this->data = new \SimpleXMLElement($this->data, LIBXML_PARSEHUGE);
        $user = User::where('id', $this->data->Player->playerGuid)->where('token', $this->data->Auth->token)->first();
        if (!$user) {
            $response = new \stdClass();
            $response->status = "FAIL";
            $response->message = "Invalid User";
            $response = $this->convert_response($response);
            return response($response, 200)->header('Content-Type', 'application/xml');
        }
        $balance = Balance::where('user_id', $this->data->Player->playerGuid)->first();
//        $response = array();
//        $response['Token'] = ['token'=>$user->token,'playerGuid'=>$user->id];
        $response = new \stdClass();
        $response->status = "SUCCESS";
        $response->Player = ['playerGuid'=>$this->data->Player->playerGuid];
        $response->Balance = [
            'currency' => 'NGA',
            'total' => $balance->balance,
            'cash' => $balance->balance,
            'restrictedCash' => 0.00,
            'bonus' => 0.00
        ];
        $response = $this->convert_response($response);
        return response($response, 200)->header('Content-Type', 'application/xml');
    }
    public function betAction(Request $request)
    {
        // convert into an object
        $this->data = $request->getContent();
        $this->data = str_replace('&', '&amp;', $this->data);
        $this->data = new \SimpleXMLElement($this->data, LIBXML_PARSEHUGE);
        $xml = simplexml_load_string($request->getContent());
        $json = json_encode($xml);
        // $user = User::where('id', $this->data->Bet->playerGuid)->where('token', $this->data->Auth->token)->first();
        $user = User::where('id', $this->data->Bet->playerGuid)->first();
        if (!$user) {
            $response = new \stdClass();
            $response->status = "FAIL";
            $response->message = "Invalid User";
            $response = $this->convert_response($response);
            return response($response, 200)->header('Content-Type', 'application/xml');
        }
        $balance = Balance::where('user_id', $this->data->Bet->playerGuid)->first();
        $openingBalance = $balance->balance;
        $amount= $this->data->BetAction->amount;
        if(!in_array($this->data->BetAction->type, ['WIN', 'CANCEL','STAKE'])) {
            $response = new \stdClass();
            $response->status = "FAIL";
            $response->message = "Status should WIN, CANCEL or STAKE";
            $response = $this->convert_response($response);
            return response($response, 200)->header('Content-Type', 'application/xml');
        }
        if($this->data->BetAction->type == 'STAKE') {
            $closingBalance = $balance->balance - $amount;
            $balance->balance = $closingBalance;
            $remarks = 'Place Bet on Sparket';
            $balance->save();
        } elseif(in_array($this->data->BetAction->type, ['WIN', 'CANCEL'])) {
            $closingBalance = $balance->balance + $amount;
            $balance->balance = $closingBalance;
            if($this->data->BetAction->type == 'WIN') {
                if($amount > 0) {
                    $remarks = 'Win Bet on Sparket';
                }else {
                    $remarks = 'Loss Bet on Sparket';
                }
            }else {
                $remarks = 'Cancel Bet on Sparket';
            }
            $balance->save();
        }

        //Save transaction
        $transaction = new Transaction();
        $transaction->opening_balance = $openingBalance;
        $transaction->closing_balance = $closingBalance;
        $transaction->amount = $amount;
        $transaction->user_id = $this->data->Bet->playerGuid;
        if($this->data->BetAction->type == 'STAKE') {
            $status = 'withdraw';
        }elseif ($this->data->BetAction->type == 'WIN') {
            $status = 'deposit';
        }else {
            $status = 'reversed';
        }
        $transaction->status = $status;
        $transaction->remarks = $remarks;
        $transaction->request = $json;
        $transaction->save();

        $response = new \stdClass();
        $response->status = "SUCCESS";
        $response->balance = $closingBalance;
        $response = $this->convert_response($response);
        return response($response, 200)->header('Content-Type', 'application/xml');
    }
    public function sportbetTransaction(Request $request)
    {
        // convert into an object
        $data = json_decode($request->getContent());
        $existing = Transaction::where('transaction_id', $data->transaction_id)
            ->where(function($q) use ($data) {
                if ($data->action == 'bet') {
                    $q->where('status', 'withdraw');
                } elseif ($data->action == 'result') {
                    $q->where('status', 'deposit');
                }
            })
            ->first();
        if($existing) {
            $response = [];
            $response['error'] = null;
            $response['transaction_id'] = $data->transaction_id;
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        $user = User::where('id', $data->user_id)->first();
        if (!$user) {
            $response = [];
            $response['error'] = 'INTERNAL_ERROR';
            $response['status'] = "FAIL";
            $response['message'] = "Invalid User";
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        $balance = Balance::where('user_id', $data->user_id)->first();
        $openingBalance = $balance->balance;
        $amount = $data->amount;
        if($data->action == 'bet') {
            if($openingBalance < $amount) {
                $response = [];
                $response['error'] = "INSUFFICENT_FUNDS";
                $response['message'] = "Insufficient Balance in your wallet";
                return response($response, 200)->header('Content-Type', 'application/json');
            }
        }
        if(!in_array($data->action, ['bet', 'result'])) {
            $response = [];
            $response['error'] = 'INTERNAL_ERROR';
            $response['status'] = "FAIL";
            $response['message'] = "Action should bet or result";
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        if($data->action == 'bet') {
            $closingBalance = $balance->balance - $amount;
            $balance->balance = $closingBalance;
            $remarks = 'Place Bet on Sportbook';
            $balance->save();
        } elseif($data->action == 'result') {
            $closingBalance = $balance->balance + $amount;
            $balance->balance = $closingBalance;
            if($amount > 0) {
                $remarks = 'Win Bet on Sportbok';
            }
            $balance->save();
        }
        //Save transaction
        $transaction = new Transaction();
        $transaction->opening_balance = $openingBalance;
        $transaction->closing_balance = $closingBalance;
        $transaction->amount = $amount;
        $transaction->user_id = $data->user_id;
        if($data->action == 'bet') {
            $status = 'withdraw';
        }elseif ($data->action == 'result') {
            $status = 'deposit';
        }
        $transaction->status = $status;
        $transaction->transaction_id = $data->transaction_id;
        $transaction->remarks = $remarks;
        $transaction->request = $request->getContent();
        $transaction->save();
        $response = [];
        $response['error'] = null;
        $response['transaction_id'] = $data->transaction_id;
        return response($response, 200)->header('Content-Type', 'application/json');
    }
    public function sportbetTransactionRollback(Request $request)
    {
        // convert into an object
        $data = json_decode($request->getContent());
        $transactions = Transaction::where('transaction_id', $data->transaction_id)->first();
        if($transactions) {
            $response = [];
            $response['error'] = null;
            $response['transaction_id'] = $data->transaction_id;
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        $user = User::where('id', $data->user_id)->first();
        if (!$user) {
            $response = [];
            $response['status'] = "FAIL";
            $response['message'] = "Invalid User";
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        $balance = Balance::where('user_id', $data->user_id)->first();
        $openingBalance = $balance->balance;
        $amount= $data->amount;
        if(!in_array($data->action, ['rollback'])) {
            $response = [];
            $response['status'] = "FAIL";
            $response['message'] = "Action should rollback";
            return response($response, 200)->header('Content-Type', 'application/json');
        }
        if($data->action == 'rollback') {
            if($data->rollback_transaction_id) {
                $rollabackDetail = Transaction::where('transaction_id', $data->rollback_transaction_id)->first();
                if($rollabackDetail->status == 'withdraw') {
                    $closingBalance = $balance->balance + $rollabackDetail->amount;
                } elseif ($rollabackDetail->status == 'deposit') {
                    $closingBalance = $balance->balance - $rollabackDetail->amount;
                }
            }
            $balance->balance = $closingBalance;
            $balance->save();
        }
        //Save transaction
        $transaction = new Transaction();
        $transaction->opening_balance = $openingBalance;
        $transaction->closing_balance = $closingBalance;
        $transaction->amount = $amount;
        $transaction->user_id = $data->user_id;
        if($rollabackDetail->status == 'withdraw') {
            $status = 'deposit';
        } elseif ($rollabackDetail->status == 'deposit') {
            $status = 'withdraw';
        }
        $transaction->status = $status;
        $transaction->transaction_id = $data->transaction_id;
        $transaction->remarks = "Rollback from Sportbook";
        $transaction->request = $request->getContent();
        $transaction->save();
        $response = [];
        $response['error'] = null;
        $response['transaction_id'] = $data->transaction_id;
        return response($response, 200)->header('Content-Type', 'application/json');
    }
}

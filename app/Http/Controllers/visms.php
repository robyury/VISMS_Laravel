<?php

namespace App\Http\Controllers;

use App\Models\AllowedServerList;
use App\Models\ServiceList;
use App\Models\ProductList;
use App\Models\UserList;
use App\Models\PurchaseLog;
use Illuminate\Http\Request;

class visms extends Controller
{
    public function Heartbeat(Request $request)
    {
        $serviceCode = $request->service_code ?? '';
        $result = 0;
        $msg = '';

        // Verifique se o serviço está registrado e ativo.
        $serviceExists = ServiceList::where('ServiceCode', $serviceCode)->where('Active', 1)->exists();
        if (!$serviceExists) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -97,
                "msg" => 'ERD_VISMS_BILL_NOT_REGISTERED',
            ], 400);
        }

        // Verifique se o IP está autorizado a usar o sistema.
        $ipAllowed = AllowedServerList::where('ServiceCode', $serviceCode)->where('IPAddr', $request->ip())->exists();
        if (!$ipAllowed) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -99,
                "msg" => 'ERD_VISMS_BILL_NOT_ALLOWED',
            ], 400);
        }

        $result = 1;

        $response = [
            "service_code" => $serviceCode,
            "Result" => $result,
            "msg" => $msg ?? 'ERD_VISMS_UNKNOWN',
        ];

        return response($response, 200);
    }

    public function ProductInquiry(Request $request)
    {
        $serviceCode = $request->service_code ?? '';
        $result = 0;
        $msg = '';

        // Verifique se o serviço está registrado e ativo.
        $serviceExists = ServiceList::where('ServiceCode', $serviceCode)->where('Active', 1)->exists();
        if (!$serviceExists) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -97,
                "msg" => 'ERD_VISMS_BILL_NOT_REGISTERED',
            ], 400);
        }

        // Verifique se o IP está autorizado a usar o sistema.
        $ipAllowed = AllowedServerList::where('ServiceCode', $serviceCode)->where('IPAddr', $request->ip())->exists();
        if (!$ipAllowed) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -99,
                "msg" => 'ERD_VISMS_BILL_NOT_ALLOWED',
            ], 400);
        }

        // Consulte a lista de produtos paginada.
        $productlist = ProductList::where('ServiceCode', $serviceCode)->paginate($request->row_per_page, ['*'], 'page_index', $request->page_index);

        // Construa a lista de produtos.
        $product_list = $productlist->map(function ($item) {
            return [
                'product_no' => (int)$item->ProductNo,
                'relation_product_no' => (int)$item->RelationProductNo,
                'product_expire' => (int)$item->ProductExpire,
                'product_pieces' => (int)$item->ProductPieces,
                'payment_type' => (int)$item->PaymentType,
                'sale_price' => (int)$item->SalePrice,
                'category_no' => (int)$item->CategoryNo,
                'bonus_product_count' => (int)$item->BonusProductCount,
                'product_id' => $item->ProductID,
                'product_guid' => $item->ProductGUID,
                'product_type' => $item->ProductType,
            ];
        });

        $result = 1;

        $response = [
            "service_code" => $serviceCode,
            "Result" => $result,
            "total_product_count" => $productlist->total(),
            "product_array_length" => $productlist->count(),
            "product_list" => $product_list,
        ];

        return response($response, 200);
    }

    public function CheckBalance(Request $request)
    {
        $serviceCode = $request->service_code ?? '';

        // Verifique se o serviço está registrado e ativo.
        $serviceExists = ServiceList::where('ServiceCode', $serviceCode)->where('Active', 1)->exists();
        if (!$serviceExists) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -97,
                "msg" => 'ERD_VISMS_BILL_NOT_REGISTERED',
            ], 400);
        }

        // Verifique se o IP está autorizado a usar o sistema.
        $ipAllowed = AllowedServerList::where('ServiceCode', $serviceCode)->where('IPAddr', $request->ip())->exists();
        if (!$ipAllowed) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -99,
                "msg" => 'ERD_VISMS_BILL_NOT_ALLOWED',
            ], 400);
        }

        // Verifique se o usuário existe no banco de dados VISMS.
        $user = UserList::where('ServiceCode', $serviceCode)->where('strNexoNID', $request->user_id)->first();
        if (!$user) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -96,
                "msg" => 'ERD_VISMS_BILL_NOTUSER_ID',
            ], 400);
        }

        return response([
            "service_code" => $serviceCode,
            "Result" => 1,
            "bonus_balance" => (int)($user->BonusBalance ?? 0),
            "real_balance" => (int)($user->RealBalance ?? 0),
        ], 200);
    }

    public function PurchaseItemRuleID(Request $request)
    {
        $serviceCode = $request->service_code ?? '';

        // Verifique se o serviço está registrado e ativo.
        $serviceExists = ServiceList::where('ServiceCode', $serviceCode)->where('Active', 1)->exists();
        if (!$serviceExists) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -97,
                "msg" => 'ERD_VISMS_BILL_NOT_REGISTERED',
            ], 400);
        }

        // Verifique se o IP está autorizado a usar o sistema.
        $ipAllowed = AllowedServerList::where('ServiceCode', $serviceCode)->where('IPAddr', $request->ip())->exists();
        if (!$ipAllowed) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -99,
                "msg" => 'ERD_VISMS_BILL_NOT_ALLOWED',
            ], 400);
        }

        // Verifique se o usuário existe no banco de dados VISMS.
        $user = UserList::where('ServiceCode', $serviceCode)->where('strNexonID', $request->user_id)->first();
        if (!$user) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -96,
                "msg" => 'ERD_VISMS_BILL_NOTUSER_ID',
            ], 400);
        }

        // Verifique o saldo apenas se nenhuma outra falha ocorreu até agora.
        if ($user->RealBalance < $request->total_amount) {
            return response([
                "service_code" => $serviceCode,
                "Result" => 12040,
                "msg" => 'ERD_VISMS_BILL_INSUFFICIENT_BALANCE',
            ], 400);
        }

        // Puxar dados do item comprado para fins de log.
        $productInfo = json_decode($request->input('product_info'), true);
        if ($productInfo !== null && is_array($productInfo) && count($productInfo) > 0) {
            $productArray = [];
            foreach ($productInfo as $product) {
                $productArray[] = [
                    "product_no" => (int)$product['product_no'],
                    "order_quantity" => (int)$product['order_quantity'],
                ];
            }

            // Preencha os dados necessários.
            $purchase_log = [
                'ServiceCode' => $serviceCode,
                'OrderID' => $request->order_id,
                'ProductNo' => $productArray[0]['product_no'],
                'PaymentType' => $request->payment_type,
                'PaymentRuleID' => $request->payment_rule_id,
                'TotalPrice' => $request->total_amount,
                'OrderAmmount' => $productArray[0]['order_quantity'],
                'oid' => $request->user_oid,
                'strNexonID' => $request->user_id,
                'IPAddress' => $request->ip(),
                'IsGift' => 0,
                'Receiver_oid' => NULL,
                'Receiver_strNexonID' => NULL,
            ];

            // Crie o registro de log na tabela VISMS_PurchaseLog.
            $SRL = PurchaseLog::insertGetId($purchase_log);

            // Atualize o saldo apenas se nenhuma outra falha ocorreu até agora.
            UserList::where('ServiceCode', $serviceCode)
                ->where('strNexonID', $request->user_id)
                ->update(['RealBalance' => \DB::raw("RealBalance - {$request->total_amount}")]);

            return response([
                "service_code" => $serviceCode,
                "Result" => 1,
                "msg" => 'ERD_VISMS_BILL_PAID_SUCCESS',
                "OrderID" => $request->order_id,
                "OrderNo" => $SRL,
                "PaymentRuleID" => (int)$request->payment_rule_id, // 1 - Collective, 2 - Balance, 3 - Prepaid
                "ProductArrayLength" => count($productInfo),
                "ProductInfo" => $productArray,
            ], 200);
        } else {
            return response([
                "service_code" => $serviceCode,
                "Result" => -96,
                "msg" => 'ERD_VISMS_BILL_PARAMETER_ERROR',
            ], 400);
        }
    }
    public function PurchaseGift(Request $request)
    {
        $serviceCode = $request->service_code ?? '';

        // Verifique se o serviço está registrado e ativo.
        $serviceExists = ServiceList::where('ServiceCode', $serviceCode)->where('Active', 1)->exists();
        if (!$serviceExists) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -97,
                "msg" => 'ERD_VISMS_BILL_NOT_REGISTERED',
            ], 400);
        }

        // Verifique se o IP está autorizado a usar o sistema.
        $ipAllowed = AllowedServerList::where('ServiceCode', $serviceCode)->where('IPAddr', $request->ip())->exists();
        if (!$ipAllowed) {
            return response([
                "service_code" => $serviceCode,
                "Result" => -99,
                "msg" => 'ERD_VISMS_BILL_NOT_ALLOWED',
            ], 400);
        }

        // Verifique se o usuário existe no banco de dados VISMS.
        $sender = UserList::where('ServiceCode', $serviceCode)->where('strNexonID', $request->sender_user_id)->first();
        if (!$sender) {
            return response([
                "service_code" => $serviceCode,
                "Result" => 12003,
                "msg" => 'ERD_VISMS_BILL_NO_PLAYER',
            ], 400);
        }

        $receiver = UserList::where('ServiceCode', $serviceCode)->where('strNexonID', $request->receiver_user_id)->first();
        if(!$receiver)
        {
            return response([
                "service_code" => $serviceCode,
                "Result" => 12002,
                "msg" => 'ERD_VISMS_BILL_RECIPIENT_NO_PLAYER',
            ], 400);
        }

        // Verifique o saldo apenas se nenhuma outra falha ocorreu até agora.
        if ($sender->RealBalance < $request->total_amount) {
            return response([
                "service_code" => $serviceCode,
                "Result" => 12040,
                "msg" => 'ERD_VISMS_BILL_INSUFFICIENT_BALANCE',
            ], 400);
        }

        // Puxar dados do item comprado para fins de log.
        $productInfo = json_decode($request->input('product_info'), true);
        if ($productInfo !== null && is_array($productInfo) && count($productInfo) > 0) {
            $productArray = [];
            foreach ($productInfo as $product) {
                $productArray[] = [
                    "product_no" => (int)$product['product_no'],
                    "order_quantity" => (int)$product['order_quantity'],
                ];
            }

            // Preencha os dados necessários.
            $purchase_log = [
                'ServiceCode' => $serviceCode,
                'OrderID' => $request->order_id,
                'ProductNo' => $productArray[0]['product_no'],
                'PaymentType' => $request->payment_type,
                'PaymentRuleID' => $request->payment_rule_id,
                'TotalPrice' => $request->total_amount,
                'OrderAmmount' => $productArray[0]['order_quantity'],
                'oid' => $request->sender_user_oid,
                'strNexonID' => $request->sender_user_id,
                'IPAddress' => $request->ip(),
                'IsGift' => 1,
                'Receiver_oid' => $request->receiver_user_oid,
                'Receiver_strNexonID' => $request->receiver_user_id,
            ];

            // Crie o registro de log na tabela VISMS_PurchaseLog.
            $SRL = PurchaseLog::insertGetId($purchase_log);

            // Atualize o saldo apenas se nenhuma outra falha ocorreu até agora.
            UserList::where('ServiceCode', $serviceCode)
                ->where('strNexonID', $request->user_id)
                ->update(['RealBalance' => \DB::raw("RealBalance - {$request->total_amount}")]);

            return response([
                "service_code" => $serviceCode,
                "Result" => 1,
                "msg" => 'ERD_VISMS_BILL_PAID_SUCCESS',
                "OrderID" => $request->order_id,
                "OrderNo" => $SRL,
                "PaymentRuleID" => (int)$request->payment_rule_id, // 1 - Collective, 2 - Balance, 3 - Prepaid
                "ProductArrayLength" => count($productInfo),
                "ProductInfo" => $productArray,
            ], 200);
        } else {
            return response([
                "service_code" => $serviceCode,
                "Result" => -96,
                "msg" => 'ERD_VISMS_BILL_PARAMETER_ERROR',
            ], 400);
        }
    }
}

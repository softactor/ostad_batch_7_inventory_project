<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        
    }
    
    public function store(Request $request)
    {

        $customer_id = $request->customer_id;
        $user_id = $request->user_id;
        $invoice_date = $request->invoice_date;
        $notes = $request->notes;

        $invoice_number = 'INV-'.date('Ymd').uniqid();

        $gross_total_amount = 0;

        $invoice = Invoice::create([
            'customer_id'=> $customer_id,
            'user_id'=> $user_id,
            'user_id'=> $user_id,
            'invoice_number'=> $invoice_number,
            'invoice_date'=> $invoice_date,
            'total_amount'=> 0,
            'status'=> "Pending",
        ]);

        $products = $request->products;
        $quantities = $request->quantity;
        $amounts = $request->amount;

        $total_amount = 0;
        foreach($products as $key=>$value)
        {
            $product_id = $products[$key];
            $quantity  = $quantities[$key];
            $amount  = $amounts[$key];

            $total_amount = $quantity * $amount;
            $gross_total_amount+= $total_amount;

            $invoiveDetails = [
                'invoice_id' =>  $invoice->id,
                'product_id' =>  $product_id,
                'quantity' =>  $quantity,
                'amount' =>  $amount,
                'total_amount' =>  $total_amount,
            ];

            InvoiceDetails::create($invoiveDetails);

            // update product table:
            $product = Product::find($product_id);
            $product->quantity = $product->quantity - $quantity;
            $product->save();
        }

        $invoice->total_amount = $gross_total_amount;
        $invoice->save();

        return response()->json([
            'status' => 'success',
            'data' => $invoice,
            'message' => 'Invoice Created successfully.'
        ]);

    }
    
    public function show(Invoice $invoice)
    {
        
    }
    public function print(Invoice $invoice)
    {
        $pdf = Pdf::loadView('pdfs.invoice', compact('invoice'));
        return $pdf->stream('customer_invoice.pdf');
    }
}

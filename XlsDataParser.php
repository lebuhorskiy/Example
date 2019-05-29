<?php
namespace App\UseCases\Parsers;
use App\Transaction;
use Asan\PHPExcel\Excel;
use Carbon\Carbon;
class XlsDataParser
{
    protected $reader;
    public function __construct($file)
    {
        if(!file_exists($file)) {
            throw new \InvalidArgumentException("File {$file} doesn't exists.");
        }
        $this->reader = Excel::load($file);
        $this->transaction_last = $transaction_last;
    }
    public function parse($transaction_last)
    {
        $title = $this->reader->current();
        $this->reader->next();
        $columns = $this->reader->current();
        $this->reader->next();
        //$reader->seek(3);
        $saved = 0;
        $skipped = 0;
        $i = 0;
        
        // dump($this->reader->current());
        while($current = $this->reader->current()) {
            list(
                $date,
                $time,
                $category,
                $card,
                $description,
                $card_sum,
                $card_currency,
                $transaction_sum,
                $transaction_currency,
                $balance,
                $balance_currency,
            ) = $current;
            // TODO understand where is problem with this case
            if($description == 'B 40') {
                continue;
            }
            $card_sum = $this->normalizeNumber($card_sum);
            $transaction_sum = $this->normalizeNumber($transaction_sum);
            $balance = $this->normalizeNumber($balance);
            $date1 = explode('.', $date);
            $date1 = $date1[2] . '-' . $date1[1] . '-' . $date1[0];
            
            
                
                $exp_date = explode('-', $transaction_last->date);
                $exp_date = $exp_date[2] . '-' . $exp_date[1] . '-' . $exp_date[0];
                $time_transaction = $transaction_last->time;
                $time_list = $current[1];
                $date_transaction = $exp_date . ' ' .$time_transaction;
                $date_list = $current[0] . ' ' .$time_list;
                if(strtotime($date_transaction) < strtotime($date_list)){
                	
                	$date = explode('.', $date);
		            $date = $date[2] . '-' . $date[1] . '-' . $date[0];
		            
		            $create = Transaction::create([
		                'date' => $date,
		                'time' => $time,
		                'category' => $category,
		                'card' => $card,
		                'description' => $description,
		                'card_sum' => $card_sum,
		                'card_currency' => $card_currency,
		                'transaction_sum' => $transaction_sum,
		                'transaction_currency' => $transaction_currency,
		                'balance' => $balance,
		                'balance_currency' => $balance_currency
		            ]);
		           
                }
                else{
                	break;
                }
            $i++;
            $saved++;
            $this->reader->next();
        }
        \Log::info("Total created {$saved} & skipped {$skipped} orders.");
    }
    public function updateCheckTime($file, Carbon $date)
    {
        \File::put($file, json_encode([
            'updated_at' => $date->format('Y-m-d H:i:s')
        ]));
    }
    public static function getMetaInfo($file)
    {
        $meta = ['updated_at' => '��� ������.'];
        if(file_exists($file)) {
            $meta = json_decode(\File::get($file), true);
        }
        return $meta;
    }
    protected function normalizeNumber($value) {
        $value = str_replace("&nbsp;", '', htmlentities($value));
        $is_negative = substr($value, 0, 1) === '-';
        preg_match('/([\.\d]+)$/', $value, $match);
        $number = (float)ltrim($match[1], '0');
        return $is_negative ? -$number : $number;
    }
}

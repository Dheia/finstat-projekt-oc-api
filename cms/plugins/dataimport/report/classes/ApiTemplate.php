<?php namespace Dataimport\Report\classes;

use Dataimport\Report\classes\Requests\TemplateRequest;
use Exception;

class ApiTemplate
{

    public $request;
    public $response;
    public $tables;

    private array $columns = [

        'revenue' => 'Výnosy z hospodárskej činnosti spolu súčet',
        'profits' => 'Výsledok hospodárenia za účtovné obdobie pred zdanením',
        'capital' => 'Vlastné imanie',

        'assets_total' => 'SPOLU MAJETOK',
        'lt_intangible_assets_total' => 'Dlhodobý nehmotný majetok',
        'lt_tangible_assets_total' => 'Dlhodobý hmotný majetok súčet',
        'lt_financial_assets_total' => 'Dlhodobý finančný majetok súčet',
        'st_receivables_total' => 'Krátkodobé pohľadávky súčet',
        'financial_accounts_total' => 'Finančný majetok',

        'liabilities_total' => 'SPOLU VLASTNÉ IMANIE A ZÁVÄZKY',
        'base_capital' => 'Základné imanie',
        'result_last_year' => 'Nerozdelený zisk alebo neuhradená strata minulých rokov',
        'profit_for_period_after_tax' => 'Výsledok hospodárenia za účtovné obdobie po zdanení',
        'reserves' => 'Krátkodobé rezervy',
        'st_liabilities' => 'Krátkodobé záväzky okrem rezerv, úverov a výpomoci súčet',
        'bank_loans' => 'Bežné bankové úvery'

    ];

    /**
     * @throws Exception
     */
    public function __construct($id)
    {
        $this->request($id);
    }

    /**
     * @throws Exception
     */
    public function request($id): void
    {
        $this->request = new TemplateRequest($id);
        $this->response = $this->request->response;
        $this->tables = collect($this->request->response['tabulky']);
    }

    public function getColumn($column): array
    {

        $column_text = $this->columns[$column];

        $filtered = $this->tables->filter( function ($value) use ($column_text) {
            return $this->filterFindRow($value['riadky'], $column_text);
        });

        $table = $filtered->first();
        $table_name = $filtered->first()['nazov']['sk'];
        $table_columns = $table['pocetDatovychStlpcov'];

        $row = $this->filterFindRow($table['riadky'], $column_text)->first();
        $row_number = $row['cisloRiadku'];

        return [
            'table_name' => $table_name,
            'row' => $row_number * $table_columns - 2,
        ];


    }

    private function filterFindRow($rows, $column_text)
    {
        $rows = collect($rows);

        $filtered = $rows->filter(function ($value) use ($column_text) {
            if (str_contains($value['text']['sk'], $column_text)) {
                return $value;
            }
        });

        if ($filtered->count() > 0) {
            return $filtered;
        }
    }

}

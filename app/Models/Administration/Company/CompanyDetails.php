<?php

namespace App\Models\Administration\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetails extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_companies_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'com_total_official_channels',
        'com_total_unofficial_channels',
        'com_total_users',
        'com_total_connected_channels',
        'com_total_messages_sent',
        'com_total_messages_received',
        'com_total_services',
        'com_total_overdue_invoices',
        'com_total_overdue_amount',
        'com_due_date_invoice',
    ];
}

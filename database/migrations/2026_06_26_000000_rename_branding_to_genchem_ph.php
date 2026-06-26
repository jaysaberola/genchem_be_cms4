<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('id', 1)->update([
            'website_name' => 'GENCHEM PH',
            'company_name' => 'GENCHEM PH',
        ]);

        DB::table('pages')
            ->where('contents', 'like', '%Precious Pages Corp.%')
            ->update([
                'contents' => DB::raw("REPLACE(contents, 'Precious Pages Corp.', 'GENCHEM PH')"),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('id', 1)->update([
            'website_name' => 'PRECIOUS PAGES CORP',
            'company_name' => 'PRECIOUS PAGES CORP',
        ]);

        DB::table('pages')
            ->where('contents', 'like', '%GENCHEM PH%')
            ->update([
                'contents' => DB::raw("REPLACE(contents, 'GENCHEM PH', 'Precious Pages Corp.')"),
            ]);
    }
};

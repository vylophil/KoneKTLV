<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** @extends ResourceController<Company> */
class CompanyController extends ResourceController
{
    /** @var class-string<Company> */
    protected string $model = Company::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'logo_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'location' => ['nullable', 'string', 'max:255'],
            'company_size' => ['nullable', 'string', 'max:50'],
            'founded_year' => ['nullable', 'integer', 'min:0', 'min:1000', 'max:'.date('Y')],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
        abort_unless(in_array($action, ['index', 'show'], true), 405, 'Manual company management has been retired.');
    }
}

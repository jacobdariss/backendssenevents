<?php

namespace Modules\Partner\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    public function rules()
    {
        $partnerId = $this->route('partner');

        $rules = [
            'name'        => 'required|string|max:255|unique:partners,name,' . $partnerId,
            'email'       => 'nullable|email|max:191',
            'phone'       => 'nullable|string|max:50',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'status'      => 'sometimes|boolean',
        ];

        // User account creation fields (only on create or if creating a new account)
        if ($this->boolean('create_account')) {
            $rules['account_first_name'] = 'required|string|max:255';
            $rules['account_last_name']  = 'required|string|max:255';
            $rules['account_email']      = 'required|email|unique:users,email';
            $rules['account_password']   = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('messages.name_required'),
            'name.unique'   => __('messages.name_already_exists'),
            'email.email'   => __('messages.email_invalid'),
        ];
    }
}

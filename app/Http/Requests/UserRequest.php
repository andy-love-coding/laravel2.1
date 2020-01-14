<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return false;
        return true; // 表示所有权限都通过
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|between:3,25|regex:/^[A-Za-z0-9\-\_]+$/|unique:users,name,' . Auth::id(),
            'email' => 'required|email',
            'introduction' => 'max:80',
            'avatar' => 'mimes:jpeg,bmp,png,jif|dimensions:min_width=208,min_length=208',
        ];
    }

    // 由于上述规则的'name'字段会被翻译成'名称'，所以此处需自定义翻译
    public function messages()
    {
        return [
            'name.required' => '用户名不能为空',
            'name.between' => '用户名必须介于 3 - 25 个字符直接。',
            'name.regex' => '用户名只支持英文、数字、横杠和下划线。',
            'name.unique' => '用户名已被占用，请重新填写',
            'avatar.dimensions' => '图片的清晰度不够，宽和高需要 208px 以上',
            'avatar.mimes' => '头像必须是 jpeg，bmp，png，gif 格式的图片',
        ];
    }
}

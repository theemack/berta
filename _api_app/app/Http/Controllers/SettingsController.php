<?php

namespace App\Http\Controllers;

use App\SiteSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    public function update(Request $request) {
        $json = $request->json()->all();
        $path_arr = explode('/', $json['path']);
        $site = $path_arr[0];
        $settings = new SiteSettings($site);

        $res = $settings->saveValueByPath($json['path'], $json['value']);
        // @@@:TODO: Replace this with something sensible, when migration to redux is done
        $res['update'] = $res['value'];
        $res['real'] = $res['value'];
        // @@@:TODO:END

        return response()->json($res);
    }

}

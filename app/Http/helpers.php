<?php

if(!function_exists('emptyDir')) {
    function emptyDir($dir) {
        if (is_dir($dir)) {
            $scn = scandir($dir);
            foreach ($scn as $files) {
                if ($files !== '.') {
                    if ($files !== '..') {
                        if (!is_dir($dir . '/' . $files)) {
                            unlink($dir . '/' . $files);
                        } else {
                            emptyDir($dir . '/' . $files);
                            rmdir($dir . '/' . $files);
                        }
                    }
                }
            }
        }
    }
}

if(!function_exists('getValueByKey')) {
    function getValueByKey($key, array $data, $default = null)
    {
        // @assert $key is a non-empty string
        // @assert $data is a loopable array
        // @otherwise return $default value
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        // @assert $key contains a dot notated string
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                // @assert $data[$innerKey] is available to continue
                // @otherwise return $default value
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        // @fallback returning value of $key in $data or $default value
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}

if(!function_exists('setEnv')) {
    function setEnv($values = array())
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}=\"{$envValue}\"\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}=\"{$envValue}\"", $str);
                }

            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) return false;
        return true;
    }
}

if(!function_exists('getLoadPageTime')) {
    function getLoadPageTime()
    {
        return microtime(true) - LARAVEL_START;
    }
}


if(!function_exists('gen_uuid')) {
    function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if(!function_exists('setLTSystem')) {
    function setLTSystem($path, $contain)
    {
        if (config('app.install'))
            return false;

        $is = tx_app_comonent()->theme()->is;

        $theme = tx_app_comonent()->theme()->get('eid');

        $isDeveloper = ($is == "Development") ? true : false;
        $isPrivate = ($is == "Private") ? true : false;

        if ($theme != 01010101) {
            if ($isDeveloper) {
                $d_path = resource_path() . '/views/Theme/Development/' . $theme . '/Assets/Custom/' . $path;

                if (File::exists($d_path) && File::get($d_path) != $contain)
                    return File::put($d_path);

                return File::put($d_path);
            }

            if ($isPrivate) {
                $pr_path = resource_path() . '/views/Theme/Private/' . $theme . '/Assets/Custom/' . $path;

                if (File::exists($pr_path) && File::get($pr_path) != $contain)
                    return File::put($pr_path);

                return File::put($pr_path);
            }

            $p_path = resource_path() . '/views/Theme/Public/' . $theme . '/Assets/Custom/' . $path;

            if (File::exists($p_path) && File::get($p_path) != $contain)
                return File::put($p_path);

            return File::put($p_path);
        } else {
            $default_path = public_path() . '/assets/Custom/' . $path;

            if (File::exists($default_path) && File::get($default_path) != $contain)
                return File::put($default_path);

            return File::put($default_path);
        }
    }
}

if(!function_exists('get_class_from_file')) {
    function get_class_from_file($path_to_file, $show_classe = false)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        //Start with a blank namespace and class
        $namespace = $class = "";

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $getting_class = true;
            }

            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {

                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {

                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];

                } else if ($token === ';') {

                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;

                }
            }

            //While we're grabbing the class name...
            if ($getting_class === true) {

                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {

                    //Store the token's value as the class name
                    $class = $token[1];

                    //Got what we need, stope here
                    break;
                }
            }
        }

        //Build the fully-qualified class name and return it
        if ($show_classe) {
            return $namespace ? $namespace . '\\' . $class : $class;
        } else {
            return $namespace ? $namespace : $class;
        }
    }
}

if(!function_exists('module_path')) {
    function module_path($path = "")
    {
        return $path == "" ? app_path('Modules') : app_path('Modules/' . $path);
    }
}

if(!function_exists('setViewForTheme')) {
    function setViewForTheme($nameview, $mode = "")
    {
        $folder_name = !theme()->isDefault() ? theme()->getFolderName() : "";
        $checkExt = explode("::", $nameview);

        if($checkExt[0] != "egame" && plugin()->exists($checkExt[0])) {
            $currentPlugin = plugin()->getPluginFolderName($checkExt[0]);
            $reformateViewName = explode($checkExt[0] . "::", $nameview)[1];
            $reformateViewName = "Themes.$folder_name.Extensions.Plugins.$currentPlugin.$reformateViewName";

            if(view()->exists($reformateViewName))
                return $mode == "brut" ? $reformateViewName : view($reformateViewName);
        } elseif(!egame()->isDefault() && $checkExt[0] == "egame") {
            $currentEGame = egame()->getCurrent()->first();
            $currentFolder = egame()->getFolderName();

            if(!theme()->isDefault() && $currentEGame != null) {
                $reformateViewName = explode("egame::", $nameview)[1];
                $reformateViewName = "Themes.$folder_name.Extensions.EGames.$currentFolder.$reformateViewName";

                if(view()->exists($reformateViewName))
                    return $mode == "brut" ? $reformateViewName : view($reformateViewName);
            }
        }

        if ($mode == "brut") {
            if (!theme()->isDefault() && view()->exists('Themes.' . theme()->getFolderName() . '.' . $nameview))
                return 'Themes.' . theme()->getFolderName() . '.' . $nameview;

            return $nameview;
        }

        if (!theme()->isDefault() && view()->exists('Themes.' . theme()->getFolderName() . '.' . $nameview))
            return view('Themes.' . theme()->getFolderName() . '.' . $nameview);

        return view($nameview);
    }
}

if(!function_exists('get_storage_file')) {
    function get_storage_file($file)
    {
        if(filter_var($file, FILTER_VALIDATE_URL))
            return $file;

        return action('Controller@storage_public_file', ['file' => $file]);
    }
}

if(!function_exists('logo_default')) {
    function logo_default()
    {
        $logo = [
            "png" => storage_path('app/public/logo_default.png'),
            "jpg" => storage_path('app/public/logo_default.jpg'),
            "gif" => storage_path('app/public/logo_default.gif')
        ];

        if (File::exists($logo['png']))
            return action('Controller@storage_public_file', ['file' => 'logo_default.png']);

        if (File::exists($logo['jpg']))
            return action('Controller@storage_public_file', ['file' => 'logo_default.jpg']);

        if (File::exists($logo['gif']))
            return action('Controller@storage_public_file', ['file' => 'logo_default.gif']);
    }
}

if(!function_exists('tx')) {
    function tx()
    {
        return new \App\Http\Controllers\Controller();
    }
}

if(!function_exists('tx_app')) {
    function tx_app()
    {
        return tx()->app();
    }
}

if(!function_exists('tx_model')) {
    function tx_model()
    {
        return tx()->model();
    }
}

if(!function_exists('tx_app_component')) {
    function tx_app_component()
    {
        return tx_app()->component();
    }
}

if(!function_exists('tx_admin')) {
    function tx_admin()
    {
        return new \App\Http\Controllers\AdminController();
    }
}

if(!function_exists('game_path')) {
    function game_path($path = "")
    {
        if($path == "")
            return app_path("Http/Game");
        return app_path("Http/Game/" . $path);
    }
}

if(!function_exists('site_config')) {
    function site_config($attr)
    {
        return tx_model()->config->getFirstRow()->{$attr};
    }
}

if(!function_exists('website')) {
    function website()
    {
        return site_config('website_name');
    }
}

if(!function_exists('user')) {
    function user($id = "")
    {
        if($id == "")
            return tx_auth()->isLogin() ? tx_auth()->getUser() : null;

        return tx_model()->user->find($id);
    }
}

if(!function_exists('users_register_count')) {
    function users_register_count()
    {
        $model = tx_model()->user;

        return [
            "all" => $model->count(),
            "today" => $model->whereDate('created_at', '=', now()::today()->toDateString())->count(),
            "month" => $model->whereDate('created_at', '=', now()::today()->month)->count()
        ];
    }
}

if(!function_exists('extensions_path')) {
    function extensions_path($path = "")
    {
        if($path == "")
            return base_path("Extensions");

        return base_path("Extensions/" . $path);
    }
}

if(!function_exists('visitors_count')) {
    function visitors_count()
    {
        return app(\App\Http\Controllers\API\Visitors\BaseController::class)->visitors;
    }
}

if(!function_exists('bypass')) {
    function bypass()
    {
        return app(\App\System\Bypass::class);
    }
}

if(!function_exists('sliders')) {
    function sliders()
    {
        if(!theme()->current()->sliderIsEnable())
            return false;

        return app('system.app')->use('users.slider')->get()->sortBy('priority')->where('show', 1);
    }
}

if(!function_exists("get_mime_type")) {
    function get_mime_type($filename) {
        $idx = explode( '.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }
}
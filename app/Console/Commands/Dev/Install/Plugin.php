<?php

use App\System\Stub;
use App\Http\Traits\App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Plugin extends Command
{
    use App;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temporalcms:plugin {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create TemporalCMS Plugin';

    private $pluginNamespace = "Extensions\\Plugins";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $round_eid = rand(000000000, 999999999);
        $wname = ucwords(Str::snake($this->argument('name')));
        $author = $this->app()->api()->get('licence')->user;

        $aliase = Str::slug($wname . "_" . $author->pseudo, "_");
        $name = ucwords(Str::snake($aliase."__{$round_eid}"));

        $config_json = json_encode([
            "name" => $wname,
            "slug" => $name,
            "version" => "1.0.0",
            "id" => "$round_eid",
            "author" => [$author->pseudo],
            "description" => "Un plugin simple pour TemporalCMS",
            "aliases" => $aliase,
            "dependencies" => [
                "CMS" => null,
                "Plugins" => []
            ],
            "providers" => [
                "{$this->pluginNamespace}\\{$name}\\Providers\\{$wname}ServiceProvider"
            ],
            "incompatibility" => []
        ], JSON_PRETTY_PRINT);

        $folders = [
            "Assets",
            "Assets/css",
            "Assets/img",
            "Assets/js",
            "App",
            "App/Controllers",
            "App/Controllers/Admin",
            "App/Controllers/API",
            "App/Events",
            "App/Listeners",
            "App/Middleware",
            "App/Models",
            "Config",
            "Config/Events",
            "DataBase",
            "DataBase/migrations",
            "DataBase/factories",
            "Providers",
            "Resources",
            "Resources/views",
            "Resources/lang",
            "Resources/lang/fr",
            "Resources/lang/en",
            "Routes"
        ];

        File::makeDirectory(extensions_path('Plugins/' . $name), 0777, true, true);

        foreach($folders as $folder) {
            File::makeDirectory(extensions_path('Plugins/' . $name . '/' . $folder), 0777, true, true);
        }

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/controller.stub'), extensions_path('Plugins/' . $name . '/App/Controllers/' . $wname . 'Controller.php')));
        $stub->render([
            "{NAMESPACE}" => "{$this->pluginNamespace}\\{$name}\\App\\Controllers",
            "{CLASS_NAME}" => $wname . 'Controller',
            "{CONTROLLER_NAME}" => "Controller"
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/controller.stub'), extensions_path('Plugins/' . $name . '/App/Controllers/Admin/HomeController.php')));
        $stub->render([
            "{NAMESPACE}" => "{$this->pluginNamespace}\\{$name}\\App\\Controllers\\Admin",
            "{CLASS_NAME}" => 'HomeController',
            "{CONTROLLER_NAME}" => "AdminController"
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/provider.stub'), extensions_path('Plugins/' . $name . '/Providers/' . $wname . 'ServiceProvider.php')));
        $stub->render([
            "{NAMESPACE}" => "{$this->pluginNamespace}\\{$name}\\Providers",
            "{MODULE_NAME}" => $name,
            "{CLASS_NAME}" => $wname . 'ServiceProvider'
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/Default/routes.stub'), extensions_path('Plugins/' . $name . '/Routes/routes.php')));
        $stub->render([
            "{ROUTE_MODYXE_NAME}" => strtolower($name),
            "{MODYXE_CLASS}" => $wname,
            "{MODYXE_NAME}" => strtolower($wname)
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/Default/admin_routes.stub'), extensions_path('Plugins/' . $name . '/Routes/admin.php')));
        $stub->render([
            "{ROUTE_MODYXE_NAME}" => strtolower($name),
            "{MODYXE_CLASS}" => 'Home',
            "{MODYXE_NAME}" => strtolower('admin.' . $wname)
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/Default/api_routes.stub'), extensions_path('Plugins/' . $name . '/Routes/api.php')));
        $stub->render([
            "{ROUTE_MODYXE_NAME}" => strtolower($name),
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/Default/RoutesProvider.stub'), extensions_path('Plugins/' . $name . '/Providers/RouteServiceProvider.php')));
        $stub->render([
            "{NAMESPACE}" => "{$this->pluginNamespace}\\{$name}\\Providers",
            "{MODYXE_NAME}" => $name
        ]);

        $stub = (new Stub(app_path('System/Extensions/Plugin/Src/Stubs/Default/EventsProvider.stub'), extensions_path('Plugins/' . $name . '/Providers/EventServiceProvider.php')));
        $stub->render([
            "{NAMESPACE}" => "{$this->pluginNamespace}\\{$name}\\Providers",
            "{MODYXE_NAME}" => $name
        ]);

        File::put(extensions_path('Plugins/' . $name . '/config.json'), $config_json);
        File::put(extensions_path('Plugins/' . $name . '/Config/NavAdmin.php'), "<?php \n\nreturn [];");
        File::put(extensions_path('Plugins/' . $name . '/Config/NavUser.php'), "<?php \n\nreturn [];");

        File::put(extensions_path('Plugins/' . $name . '/Config/Events/afterActivate.php'), "<?php \n\nreturn;");
        File::put(extensions_path('Plugins/' . $name . '/Config/Events/afterUpdate.php'), "<?php \n\nreturn;");

        plugin()->enable($round_eid);

        $this->alert("$name is created, eid of this plugin is $round_eid and slug is $aliase");
    }
}
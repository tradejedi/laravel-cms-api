<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use Illuminate\Routing\Controller as BaseController;

/**
 * Common ancestor for all v1 API controllers. Sits on top of the framework
 * controller (not the host app's controller) so the package stays decoupled
 * from any specific host application namespace.
 */
abstract class Controller extends BaseController {}

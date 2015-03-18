<?php
namespace Harmony\Installer;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The goal of this installer is to redirect the user to the install screen if no user has been defined.
 */
class InstallerMiddleware implements HttpKernelInterface
{

    /**
     * @var HttpKernelInterface
     */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (!file_exists(__DIR__.'/../../../generated/users.php')) {
            $uri = substr($request->getRequestUri(), strlen(ROOT_URL));

            if (strpos($uri, "install/") !== 0) {
                return new RedirectResponse(ROOT_URL.'install/');
            }
        }

        return $this->app->handle($request, $type, $catch);
    }
}

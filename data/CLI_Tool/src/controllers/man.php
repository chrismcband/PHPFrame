<?php
/**
 * data/CLITool/src/controllers/man.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Manual controller.
 *
 * @category PHPFrame
 * @package  PHPFrame_CLITool
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class ManController extends PHPFrame_ActionController
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "index");
    }

    /**
     * Show manual.
     *
     * @return void
     * @since  1.0
     */
    public function index()
    {
        $app_doc = new PHPFrame_AppDoc($this->app()->getInstallDir());

        $str  = PHPFrame::version()."\n\n";
        $str .= "\n".$this->helper("cli")->formatH2("Usage instructions")."\n";

        $str .= "To use the command line tool you will need to specify at ";
        $str .= "least a controller,\nand normally also an action and a number";
        $str .= " of parameters. For example, to get\na configuration parameter";
        $str .= " we would use the 'get' action in the 'config'\ncontroller. ";
        $str .= "The get action takes a parameter named 'key'.\n\n";
        $str .= "phpframe config get key=db.enable\n\n";
        $str .= "The above command will show the value of db.enable as defined ";
        $str .= "in the config\nfile.\n\n";

        $str .= (string) $app_doc;

        $this->response()->title($this->config()->get("app_name"));
        $this->response()->body($str);
    }
}
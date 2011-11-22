<?
class Captcha {
    /**
     * The width of the captcha image
     * @var int
     */
    public $image_width = 100;

    /**
     * The height of the captcha image
     * @var int
     */
    public $image_height = 35;

    /**
     * The background color of the captcha
     * @var Captcha_Color
     */
    public $image_bg_color;

    /**
     * The color of the captcha text
     * @var Captcha_Color
     */
    public $text_color;

    /**
     * The length of the captcha code
     * @var int
     */
    public $code_length = 4;

    /**
     * The character set to use for generating the captcha code
     * @var string
     */
    public $charset = '123456789';
    public $namespace;

    /**
     * The font file to use to draw the captcha code, leave blank for default font AHGBold.ttf
     * @var string
     */
    protected $ttf_file;
    protected $im;
    protected $bgimg;
    protected $iscale = 1;
    protected $code;
    protected $captcha_code;
    protected $gdbgcolor;
    protected $gdtextcolor;

    /**
     * $options = array(
     *     'text_color' => new Color('#013020'),
     *     'code_length' => 5,
     *     'font_file' => 'font/custom.ttf'
     * );
     * @param array $options 
     */
    public function __construct($options = array()) {
        if (is_array($options) && sizeof($options) > 0) {
            foreach ($options as $prop => $val) {
                $this->$prop = $val;
            }
        }

        $this->image_bg_color = $this->initColor($this->image_bg_color, '#DCC099');
        $this->text_color = $this->initColor($this->text_color, '#C01DAB');

        if ($this->ttf_file == null) {
            $this->ttf_file = $_SERVER['DOCUMENT_ROOT'] . '/font/arial_bold.ttf';
        }

        if ($this->code_length == null || $this->code_length < 1) {
            $this->code_length = 4;
        }

        if (null == $this->namespace || !is_string($this->namespace)) {
            $this->namespace = 'default';
        }

        // Initialize session or attach to existing
        if (session_id() == '') { // no session has been started yet, which is needed for validation
            session_start();
        }
    }

    /**
     * Used to serve a captcha image to the browser
     */
    public function show($background_image = '') {
        if ($background_image != '' && is_readable($background_image)) {
            $this->bgimg = $background_image;
        }

        $this->doImage();
    }

    /**
     * Check a submitted code against the stored value
     * @param string $code  The captcha code to check
     * <code>
     * $code = $_POST['code'];
     * $img  = new Securimage();
     * if ($img->check($code) == true) {
     *     $captcha_valid = true;
     * } else {
     *     $captcha_valid = false;
     * }
     * </code>
     */
    public function check($code) {
        $this->code_entered = $code;
        $this->validate();
        return $this->correct_code;
    }

    /**
     * The main image drawing routing, responsible for constructing the entire image and serving it
     */
    protected function doImage() {
        $this->im = imagecreatetruecolor($this->image_width, $this->image_height);
        $this->tmpimg = imagecreatetruecolor($this->image_width, $this->image_height);

        $this->allocateColors();
        $this->setBackground();
        $this->createCode();
        $this->drawWord();
        $this->distortedCopy();
        $this->output();
    }

    /**
     * Allocate the colors to be used for the image
     */
    protected function allocateColors() {
        // allocate bg color first for imagecreate
        $this->gdbgcolor = imagecolorallocate($this->tmpimg, $this->image_bg_color->r, $this->image_bg_color->g, $this->image_bg_color->b);
        $this->gdtextcolor = imagecolorallocate($this->tmpimg, $this->text_color->r, $this->text_color->g, $this->text_color->b);
    }

    /**
     * The the background color, or background image to be used
     */
    protected function setBackground() {
        // set background color of image by drawing a rectangle since imagecreatetruecolor doesn't set a bg color
        imagefilledrectangle($this->im, 0, 0, $this->image_width, $this->image_height, $this->gdbgcolor);
        imagefilledrectangle($this->tmpimg, 0, 0, $this->image_width * $this->iscale, $this->image_height * $this->iscale, $this->gdbgcolor);
    }

    /**
     * Generates the code
     */
    protected function createCode() {
        $this->code = $this->generateCode($this->code_length);
        $this->saveData();
    }

    /**
     * Draws the captcha code on the image
     */
    protected function drawWord() {
        $font_size = $this->image_height * .9;
        $bb = imageftbbox($font_size, 0, $this->ttf_file, $this->code);
        $tx = $bb[4] - $bb[0];
        $ty = $bb[5] - $bb[1];
        $x = floor($this->image_width / 2 - $tx / 2 - $bb[0]);
        $y = round($this->image_height / 2 - $ty / 2 - $bb[1]);

        imagettftext($this->tmpimg, $font_size, 0, $x, $y, $this->gdtextcolor, $this->ttf_file, $this->code);
        // DEBUG
//        $this->im = $this->tmpimg;
//        $this->output();
    }

    /**
     * Copies the captcha image to the final image with distortion applied
     */
    protected function distortedCopy() {
        $x = 1;
        $i = 0;
        while ($x < $this->image_width) { // идем по X-су и копируем кусочки
            $xx = mt_rand(1, 2);   // c этим промежутком можно поиграть
            $yy = mt_rand(1, 5); // c этим промежутком можно поиграть
            $i = $i + ($xx / 10);         // шаг для Sin-уса
            $y = ceil(sin($i) * $yy); // смещение по Y-ку
            @imagecopy($this->im, $this->tmpimg, $x, $y, $x, 0, 1, $this->image_height);
            $x++;
        }
    }

    /**
     * Sends the appropriate image and cache headers and outputs image to the browser
     */
    protected function output() {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: image/png");
        imagepng($this->im);
        imagedestroy($this->im);
        exit();
    }

    /**
     * Generates a random captcha code from the set character set
     */
    protected function generateCode() {
        $code = '';
        for ($i = 1, $cslen = strlen($this->charset); $i <= $this->code_length; ++$i) {
            $code .= $this->charset{rand(0, $cslen - 1)};
        }
//        return 'testing';  // debug, set the code to given string
        return $code;
    }

    /**
     * Checks the entered code against the value stored in the session or sqlite database, handles case sensitivity
     * Also clears the stored codes if the code was entered correctly to prevent re-use
     */
    protected function validate() {
        $code = $this->getCode();
        $this->correct_code = false;

        if ($code != '' && $code == $this->code_entered) {
            $this->correct_code = true;
        }
        if (is_array($_SESSION['captcha'])) {
            $_SESSION['captcha'][$this->namespace] = '';
        }
    }

    /**
     * Return the code from the session or sqlite database if used.  If none exists yet, an empty string is returned
     */
    protected function getCode() {
        if (isset($_SESSION['captcha']) && is_array($_SESSION['captcha'])) {
            if (isset($_SESSION['captcha'][$this->namespace]) && trim($_SESSION['captcha'][$this->namespace]) != '') {
                return $_SESSION['captcha'][$this->namespace];
            }
        } else if (isset($_SESSION['captcha'])){
            return $_SESSION['captcha'];
        }
        return '';
    }

    /**
     * Save data to session namespace and database if used
     */
    protected function saveData() {
        if (isset($_SESSION['captcha']) && is_array($_SESSION['captcha'])) {
            $_SESSION['captcha'][$this->namespace] = $this->code;
        } else {
            $_SESSION['captcha'] = array($this->namespace => $this->code);
        }
    }

    /**
     * Convert an html color code to a Securimage_Color
     * @param string $color
     * @param Captcha_Color $default The defalt color to use if $color is invalid
     */
    protected function initColor($color, $default) {
        if ($color == null) {
            return new Captcha_Color($default);
        } else if (is_string($color)) {
            try {
                return new Captcha_Color($color);
            } catch (Exception $e) {
                return new Captcha_Color($default);
            }
        } else if (is_array($color) && sizeof($color) == 3) {
            return new Captcha_Color($color[0], $color[1], $color[2]);
        } else {
            return new Captcha_Color($default);
        }
    }
}
class Captcha_Color {
    public $r;
    public $g;
    public $b;

    public function __construct($color = '#ffffff') {
        $args = func_get_args();

        if (sizeof($args) == 0) {
            $this->r = 255;
            $this->g = 255;
            $this->b = 255;
        } else if (sizeof($args) == 1) {
            // set based on html code
            if (substr($color, 0, 1) == '#') {
                $color = substr($color, 1);
            }

            if (strlen($color) != 3 && strlen($color) != 6) {
                throw new InvalidArgumentException(
                    'Invalid HTML color code passed to Securimage_Color'
                );
            }

            $this->constructHTML($color);
        } else if (sizeof($args) == 3) {
            $this->constructRGB($args[0], $args[1], $args[2]);
        } else {
            throw new InvalidArgumentException(
                'Securimage_Color constructor expects 0, 1 or 3 arguments; ' . sizeof($args) . ' given'
            );
        }
    }

    /**
     * Construct from an rgb triplet
     * @param int $red The red component, 0-255
     * @param int $green The green component, 0-255
     * @param int $blue The blue component, 0-255
     */
    protected function constructRGB($red, $green, $blue) {
        if ($red < 0) $red = 0;
        if ($red > 255) $red = 255;
        if ($green < 0) $green = 0;
        if ($green > 255) $green = 255;
        if ($blue < 0) $blue = 0;
        if ($blue > 255) $blue = 255;

        $this->r = $red;
        $this->g = $green;
        $this->b = $blue;
    }

    /**
     * Construct from an html hex color code
     * @param string $color
     */
    protected function constructHTML($color) {
        if (strlen($color) == 3) {
            $red = str_repeat(substr($color, 0, 1), 2);
            $green = str_repeat(substr($color, 1, 1), 2);
            $blue = str_repeat(substr($color, 2, 1), 2);
        } else {
            $red = substr($color, 0, 2);
            $green = substr($color, 2, 2);
            $blue = substr($color, 4, 2);
        }

        $this->r = hexdec($red);
        $this->g = hexdec($green);
        $this->b = hexdec($blue);
    }
}
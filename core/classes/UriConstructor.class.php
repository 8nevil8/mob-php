<?
class UriConstructor {
    public $arr;

    public function __construct($arr = false) {
        $this->arr = $arr ? $arr : Ajax::pureGet();
    }

    public function put($key, $val) {
        $this->arr = array_replace_recursive($this->arr, array($key => $val));
        return $this;
    }

    public function remove($key) {
        unset($this->arr[$key]);
        return $this;
    }

    public function clear() {
        $this->arr = array();
        return $this;
    }

    public function set($action /* .... */) {
        $this->arr['action'] = $action;
        $params = func_get_args();
        array_shift($params);
        if (count($params) == 1 && is_array($params[0])) $params = $params[0];
        foreach ($params as $key => $one) {
            $this->arr[$action][$key] = $one;
        }
        return $this;
    }

    public function setAct($action /* .... */) {
        $params = func_get_args();
        return call_user_func_array(
                array($this, "set"), $params
        );
    }

    public function combine($params) {
        if (func_num_args() == 2) $params = join("=", func_get_args());
        if (is_string($params)) parse_str($params, $params);
        $this->arr = array_merge($this->arr, $params);
        foreach ($this->arr as $key => $val) if ($val == '?') unset($this->arr[$key]);
        return $this;
    }

    public function url($path = false) {
        if (!$path) $path = $_SERVER['SCRIPT_NAME'];
        if (count($this->arr) > 0) {
            return $path . '?' . $this;
        } else {
            return $path;
        }
    }

    public function __toString() {
        return http_build_query($this->arr);
    }

    public function asArray() {
        return $this->arr;
    }
}
?>
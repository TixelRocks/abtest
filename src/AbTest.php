<?php

namespace Tixel\AbTest;

use App\User;
use Illuminate\Support\Str;

class AbTest
{
    const CONTROL = 'control';

    const TREATMENT = 'treatment';

    public static $fallback = self::CONTROL;

    const COOKIE_NAME = 'abTest';

    protected $id;

    protected $descriptionCharacterLimit = 16;

    /**
     * @var string
     **/
    private $version;

    public function __construct(string $version = null)
    {
        if (empty($version) || ! in_array(mb_strtolower($version), [static::CONTROL, static::TREATMENT], true)) {
            $version = static::$fallback;
        }

        $this->version = $version;
    }

    /**
     * @param $id
     * @return AbTest
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param User $user
     * @return static
     */
    public static function forUser(User $user)
    {
        return new static($user->id % 2 ? static::CONTROL : static::TREATMENT);
    }

    public function isControl(): bool
    {
        return $this->is(static::CONTROL);
    }

    public function isTreatment(): bool
    {
        return $this->is(static::TREATMENT);
    }

    /**
     * @param $name
     * @return array
     */
    protected function getConfig($name)
    {
        return collect(config('ab'))->first(fn ($e) => $e['name'] == $name);
    }

    /**
     * @param $name
     * @return string
     */
    public function display($name)
    {
        if (! $config = $this->getConfig($name)) {
            return $name;
        }

        return $config[$this->version()] ?? $config[self::CONTROL] ?? $config[self::TREATMENT] ?? $name;
    }

    private function is(string $version): string
    {
        return $this->version === $version;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function description()
    {
        $version = $this->isControl() ? 'A' : 'B';
        $shortened = mb_substr($this->display($this->id()), 0, $this->descriptionCharacterLimit);

        if (mb_strlen($this->display($this->id())) > $this->descriptionCharacterLimit) {
            $shortened .= "...";
        }

        return "Version {$version} – {$shortened}";
    }

    public function __toString(): string
    {
        return $this->display($this->id());
    }

    public static function randomise()
    {
        self::$fallback = rand(0, 1) ? self::CONTROL : self::TREATMENT;
    }
}

<?php

namespace Ondapresswp\WPBones\Support;

if (!defined('ABSPATH')) {
  exit();
}

class Str
{
  /**
   * The cache of snake-cased words.
   *
   * @var array
   */
  protected static $snakeCache = [];

  /**
   * The cache of camel-cased words.
   *
   * @var array
   */
  protected static $camelCache = [];

  /**
   * The cache of studly-cased words.
   *
   * @var array
   */
  protected static $studlyCache = [];

  /**
   * Transliterate a UTF-8 value to ASCII.
   *
   * @param  string $value
   * @return string
   */
  public static function ascii(string $value): string
  {
    foreach (static::charsArray() as $key => $val) {
      $value = str_replace($val, $key, $value);
    }

    return preg_replace('/[^\x20-\x7E]/u', '', $value);
  }

  /**
   * Convert a value to camel case.
   *
   * @param  string $value
   * @return string
   */
  public static function camel(string $value): string
  {
    if (isset(static::$camelCache[$value])) {
      return static::$camelCache[$value];
    }

    return static::$camelCache[$value] = lcfirst(static::studly($value));
  }

  /**
   * Determine if a given string contains a given substring.
   *
   * @param  string       $haystack
   * @param  string|array $needles
   * @return bool
   */
  public static function contains(string $haystack, $needles): bool
  {
    foreach ((array) $needles as $needle) {
      if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
        return true;
      }
    }

    return false;
  }

  /**
   * Determine if a given string ends with a given substring.
   *
   * @param  string       $haystack
   * @param  string|array $needles
   * @return bool
   */
  public static function endsWith(string $haystack, $needles): bool
  {
    foreach ((array) $needles as $needle) {
      if ((string) $needle === static::substr($haystack, -static::length($needle))) {
        return true;
      }
    }

    return false;
  }

  /**
   * Cap a string with a single instance of a given value.
   *
   * @param  string $value
   * @param  string $cap
   *
   * @return string
   */
  public static function finish(string $value, string $cap)
  {
    $quoted = preg_quote($cap, '/');

    return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
  }

  /**
   * Determine if a given string matches a given pattern.
   *
   * @param  string $pattern
   * @param  string $value
   * @return bool
   */
  public static function is(string $pattern, string $value): bool
  {
    if ($pattern == $value) {
      return true;
    }

    $pattern = preg_quote($pattern, '#');

    // Asterisks are translated into zero-or-more regular expression wildcards
    // to make it convenient to check if the strings starts with the given
    // pattern such as "library/*", making any string check convenient.
    $pattern = str_replace('\*', '.*', $pattern);

    return (bool) preg_match('#^' . $pattern . '\z#u', $value);
  }

  /**
   * Return the length of the given string.
   *
   * @param  string $value
   * @return int
   */
  public static function length(string $value): int
  {
    return mb_strlen($value);
  }

  /**
   * Limit the number of characters in a string.
   *
   * @param  string $value
   * @param  int    $limit
   * @param  string $end
   * @return string
   */
  public static function limit(string $value, $limit = 100, string $end = '...'): string
  {
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
      return $value;
    }

    return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
  }

  /**
   * Convert the given string to lower-case.
   *
   * @param  string $value
   * @return string
   */
  public static function lower(string $value): string
  {
    return mb_strtolower($value, 'UTF-8');
  }

  /**
   * Limit the number of words in a string.
   *
   * @param  string $value
   * @param  int    $words
   * @param  string $end
   * @return string
   */
  public static function words(string $value, int $words = 100, string $end = '...'): string
  {
    preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

    if (!isset($matches[0]) || static::length($value) === static::length($matches[0])) {
      return $value;
    }

    return rtrim($matches[0]) . $end;
  }

  /**
   * Parse a "Class@method" style callback into class and method.
   *
   * @param  string $callback
   * @param  string $default
   * @return array
   */
  public static function parseCallback(string $callback, string $default = '')
  {
    return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
  }

  /**
   * Generate a more truly "random" alpha-numeric string.
   *
   * @param int $length
   * @return string
   * @throws \Exception
   */
  public static function random(int $length = 16): string
  {
    $string = '';

    while (($len = static::length($string)) < $length) {
      $size = $length - $len;

      $bytes = random_bytes($size);

      $string .= static::substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }

    return $string;
  }

  /**
   * Generate a more truly "random" bytes.
   *
   * @param int $length
   * @return string
   *
   * @throws \Exception
   * @deprecated since version 5.2. Use random_bytes instead.
   */
  public static function randomBytes($length = 16)
  {
    return random_bytes($length);
  }

  /**
   * Generate a "random" alpha-numeric string.
   *
   * Should not be considered sufficient for cryptography, etc.
   *
   * @param  int $length
   * @return string
   */
  public static function quickRandom(int $length = 16)
  {
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return static::substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
  }

  /**
   * Compares two strings using a constant-time algorithm.
   *
   * Note: This method will leak length information.
   *
   * Note: Adapted from Symfony\Component\Security\Core\Util\StringUtils.
   *
   * @param  string $knownString
   * @param  string $userInput
   * @return bool
   *
   * @deprecated since version 5.2. Use hash_equals instead.
   */
  public static function equals(string $knownString, string $userInput): bool
  {
    return hash_equals($knownString, $userInput);
  }

  /**
   * Replace the first occurrence of a given value in the string.
   *
   * @param  string $search
   * @param  string $replace
   * @param  string $subject
   * @return string
   */
  public static function replaceFirst(string $search, string $replace, string $subject): string
  {
    $position = strpos($subject, $search);

    if ($position !== false) {
      return substr_replace($subject, $replace, $position, strlen($search));
    }

    return $subject;
  }

  /**
   * Replace the last occurrence of a given value in the string.
   *
   * @param  string $search
   * @param  string $replace
   * @param  string $subject
   * @return string
   */
  public static function replaceLast(string $search, string $replace, string $subject): string
  {
    $position = strrpos($subject, $search);

    if ($position !== false) {
      return substr_replace($subject, $replace, $position, strlen($search));
    }

    return $subject;
  }

  /**
   * Convert the given string to upper-case.
   *
   * @param  string $value
   * @return string
   */
  public static function upper(string $value): string
  {
    return mb_strtoupper($value, 'UTF-8');
  }

  /**
   * Convert the given string to title case.
   *
   * @param  string $value
   * @return string
   */
  public static function title(string $value): string
  {
    return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
  }

  /**
   * Generate a URL friendly "slug" from a given string.
   *
   * @param  string $title
   * @param  string $separator
   * @return string
   */
  public static function slug(string $title, string $separator = '-'): string
  {
    $title = static::ascii($title);

    // Convert all dashes/underscores into separator
    $flip = $separator == '-' ? '_' : '-';

    $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));

    // Replace all separator characters and whitespace by a single separator
    $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

    return trim($title, $separator);
  }

  /**
   * Convert a string to snake case.
   *
   * @param  string $value
   * @param  string $delimiter
   * @return string
   */
  public static function snake(string $value, string $delimiter = '_'): string
  {
    $key = $value;

    if (isset(static::$snakeCache[$key][$delimiter])) {
      return static::$snakeCache[$key][$delimiter];
    }

    if (!ctype_lower($value)) {
      $value = preg_replace('/\s+/u', '', $value);

      $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    }

    return static::$snakeCache[$key][$delimiter] = $value;
  }

  /**
   * Determine if a given string starts with a given substring.
   *
   * @param  string       $haystack
   * @param  string|array $needles
   * @return bool
   */
  public static function startsWith(string $haystack, $needles): bool
  {
    foreach ((array) $needles as $needle) {
      if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
        return true;
      }
    }

    return false;
  }

  /**
   * Convert a value to studly caps case.
   *
   * @param  string $value
   * @return string
   */
  public static function studly(string $value): string
  {
    $key = $value;

    if (isset(static::$studlyCache[$key])) {
      return static::$studlyCache[$key];
    }

    $value = ucwords(str_replace(['-', '_'], ' ', $value));

    return static::$studlyCache[$key] = str_replace(' ', '', $value);
  }

  /**
   * Returns the portion of string specified by the start and length parameters.
   *
   * @param  string   $string
   * @param  int      $start
   * @param  int|null $length
   * @return string
   */
  public static function substr(string $string, int $start, int $length = null): string
  {
    return mb_substr($string, $start, $length, 'UTF-8');
  }

  /**
   * Make a string's first character uppercase.
   *
   * @param  string $string
   * @return string
   */
  public static function ucfirst(string $string): string
  {
    return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
  }

  /**
   * Returns the replacements for the ascii method.
   *
   * Note: Adapted from Stringy\Stringy.
   *
   * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
   *
   * @return array
   */
  protected static function charsArray(): array
  {
    static $charsArray;

    if (isset($charsArray)) {
      return $charsArray;
    }

    return $charsArray = [
      '0' => ['°', '₀', '۰'],
      '1' => ['¹', '₁', '۱'],
      '2' => ['²', '₂', '۲'],
      '3' => ['³', '₃', '۳'],
      '4' => ['⁴', '₄', '۴', '٤'],
      '5' => ['⁵', '₅', '۵', '٥'],
      '6' => ['⁶', '₆', '۶', '٦'],
      '7' => ['⁷', '₇', '۷'],
      '8' => ['⁸', '₈', '۸'],
      '9' => ['⁹', '₉', '۹'],
      'a' => [
        'à',
        'á',
        'ả',
        'ã',
        'ạ',
        'ă',
        'ắ',
        'ằ',
        'ẳ',
        'ẵ',
        'ặ',
        'â',
        'ấ',
        'ầ',
        'ẩ',
        'ẫ',
        'ậ',
        'ā',
        'ą',
        'å',
        'α',
        'ά',
        'ἀ',
        'ἁ',
        'ἂ',
        'ἃ',
        'ἄ',
        'ἅ',
        'ἆ',
        'ἇ',
        'ᾀ',
        'ᾁ',
        'ᾂ',
        'ᾃ',
        'ᾄ',
        'ᾅ',
        'ᾆ',
        'ᾇ',
        'ὰ',
        'ά',
        'ᾰ',
        'ᾱ',
        'ᾲ',
        'ᾳ',
        'ᾴ',
        'ᾶ',
        'ᾷ',
        'а',
        'أ',
        'အ',
        'ာ',
        'ါ',
        'ǻ',
        'ǎ',
        'ª',
        'ა',
        'अ',
        'ا',
      ],
      'b' => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
      'c' => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
      'd' => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
      'e' => [
        'é',
        'è',
        'ẻ',
        'ẽ',
        'ẹ',
        'ê',
        'ế',
        'ề',
        'ể',
        'ễ',
        'ệ',
        'ë',
        'ē',
        'ę',
        'ě',
        'ĕ',
        'ė',
        'ε',
        'έ',
        'ἐ',
        'ἑ',
        'ἒ',
        'ἓ',
        'ἔ',
        'ἕ',
        'ὲ',
        'έ',
        'е',
        'ё',
        'э',
        'є',
        'ə',
        'ဧ',
        'ေ',
        'ဲ',
        'ე',
        'ए',
        'إ',
        'ئ',
      ],
      'f' => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
      'g' => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
      'h' => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
      'i' => [
        'í',
        'ì',
        'ỉ',
        'ĩ',
        'ị',
        'î',
        'ï',
        'ī',
        'ĭ',
        'į',
        'ı',
        'ι',
        'ί',
        'ϊ',
        'ΐ',
        'ἰ',
        'ἱ',
        'ἲ',
        'ἳ',
        'ἴ',
        'ἵ',
        'ἶ',
        'ἷ',
        'ὶ',
        'ί',
        'ῐ',
        'ῑ',
        'ῒ',
        'ΐ',
        'ῖ',
        'ῗ',
        'і',
        'ї',
        'и',
        'ဣ',
        'ိ',
        'ီ',
        'ည်',
        'ǐ',
        'ი',
        'इ',
      ],
      'j' => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
      'k' => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
      'l' => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
      'm' => ['м', 'μ', 'م', 'မ', 'მ'],
      'n' => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
      'o' => [
        'ó',
        'ò',
        'ỏ',
        'õ',
        'ọ',
        'ô',
        'ố',
        'ồ',
        'ổ',
        'ỗ',
        'ộ',
        'ơ',
        'ớ',
        'ờ',
        'ở',
        'ỡ',
        'ợ',
        'ø',
        'ō',
        'ő',
        'ŏ',
        'ο',
        'ὀ',
        'ὁ',
        'ὂ',
        'ὃ',
        'ὄ',
        'ὅ',
        'ὸ',
        'ό',
        'о',
        'و',
        'θ',
        'ို',
        'ǒ',
        'ǿ',
        'º',
        'ო',
        'ओ',
      ],
      'p' => ['п', 'π', 'ပ', 'პ', 'پ'],
      'q' => ['ყ'],
      'r' => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
      's' => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
      't' => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
      'u' => [
        'ú',
        'ù',
        'ủ',
        'ũ',
        'ụ',
        'ư',
        'ứ',
        'ừ',
        'ử',
        'ữ',
        'ự',
        'û',
        'ū',
        'ů',
        'ű',
        'ŭ',
        'ų',
        'µ',
        'у',
        'ဉ',
        'ု',
        'ူ',
        'ǔ',
        'ǖ',
        'ǘ',
        'ǚ',
        'ǜ',
        'უ',
        'उ',
      ],
      'v' => ['в', 'ვ', 'ϐ'],
      'w' => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
      'x' => ['χ', 'ξ'],
      'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
      'z' => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
      'aa' => ['ع', 'आ', 'آ'],
      'ae' => ['ä', 'æ', 'ǽ'],
      'ai' => ['ऐ'],
      'at' => ['@'],
      'ch' => ['ч', 'ჩ', 'ჭ', 'چ'],
      'dj' => ['ђ', 'đ'],
      'dz' => ['џ', 'ძ'],
      'ei' => ['ऍ'],
      'gh' => ['غ', 'ღ'],
      'ii' => ['ई'],
      'ij' => ['ĳ'],
      'kh' => ['х', 'خ', 'ხ'],
      'lj' => ['љ'],
      'nj' => ['њ'],
      'oe' => ['ö', 'œ', 'ؤ'],
      'oi' => ['ऑ'],
      'oii' => ['ऒ'],
      'ps' => ['ψ'],
      'sh' => ['ш', 'შ', 'ش'],
      'shch' => ['щ'],
      'ss' => ['ß'],
      'sx' => ['ŝ'],
      'th' => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
      'ts' => ['ц', 'ც', 'წ'],
      'ue' => ['ü'],
      'uu' => ['ऊ'],
      'ya' => ['я'],
      'yu' => ['ю'],
      'zh' => ['ж', 'ჟ', 'ژ'],
      '(c)' => ['©'],
      'A' => [
        'Á',
        'À',
        'Ả',
        'Ã',
        'Ạ',
        'Ă',
        'Ắ',
        'Ằ',
        'Ẳ',
        'Ẵ',
        'Ặ',
        'Â',
        'Ấ',
        'Ầ',
        'Ẩ',
        'Ẫ',
        'Ậ',
        'Å',
        'Ā',
        'Ą',
        'Α',
        'Ά',
        'Ἀ',
        'Ἁ',
        'Ἂ',
        'Ἃ',
        'Ἄ',
        'Ἅ',
        'Ἆ',
        'Ἇ',
        'ᾈ',
        'ᾉ',
        'ᾊ',
        'ᾋ',
        'ᾌ',
        'ᾍ',
        'ᾎ',
        'ᾏ',
        'Ᾰ',
        'Ᾱ',
        'Ὰ',
        'Ά',
        'ᾼ',
        'А',
        'Ǻ',
        'Ǎ',
      ],
      'B' => ['Б', 'Β', 'ब'],
      'C' => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
      'D' => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
      'E' => [
        'É',
        'È',
        'Ẻ',
        'Ẽ',
        'Ẹ',
        'Ê',
        'Ế',
        'Ề',
        'Ể',
        'Ễ',
        'Ệ',
        'Ë',
        'Ē',
        'Ę',
        'Ě',
        'Ĕ',
        'Ė',
        'Ε',
        'Έ',
        'Ἐ',
        'Ἑ',
        'Ἒ',
        'Ἓ',
        'Ἔ',
        'Ἕ',
        'Έ',
        'Ὲ',
        'Е',
        'Ё',
        'Э',
        'Є',
        'Ə',
      ],
      'F' => ['Ф', 'Φ'],
      'G' => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
      'H' => ['Η', 'Ή', 'Ħ'],
      'I' => [
        'Í',
        'Ì',
        'Ỉ',
        'Ĩ',
        'Ị',
        'Î',
        'Ï',
        'Ī',
        'Ĭ',
        'Į',
        'İ',
        'Ι',
        'Ί',
        'Ϊ',
        'Ἰ',
        'Ἱ',
        'Ἳ',
        'Ἴ',
        'Ἵ',
        'Ἶ',
        'Ἷ',
        'Ῐ',
        'Ῑ',
        'Ὶ',
        'Ί',
        'И',
        'І',
        'Ї',
        'Ǐ',
        'ϒ',
      ],
      'K' => ['К', 'Κ'],
      'L' => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
      'M' => ['М', 'Μ'],
      'N' => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
      'O' => [
        'Ó',
        'Ò',
        'Ỏ',
        'Õ',
        'Ọ',
        'Ô',
        'Ố',
        'Ồ',
        'Ổ',
        'Ỗ',
        'Ộ',
        'Ơ',
        'Ớ',
        'Ờ',
        'Ở',
        'Ỡ',
        'Ợ',
        'Ø',
        'Ō',
        'Ő',
        'Ŏ',
        'Ο',
        'Ό',
        'Ὀ',
        'Ὁ',
        'Ὂ',
        'Ὃ',
        'Ὄ',
        'Ὅ',
        'Ὸ',
        'Ό',
        'О',
        'Θ',
        'Ө',
        'Ǒ',
        'Ǿ',
      ],
      'P' => ['П', 'Π'],
      'R' => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
      'S' => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
      'T' => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
      'U' => [
        'Ú',
        'Ù',
        'Ủ',
        'Ũ',
        'Ụ',
        'Ư',
        'Ứ',
        'Ừ',
        'Ử',
        'Ữ',
        'Ự',
        'Û',
        'Ū',
        'Ů',
        'Ű',
        'Ŭ',
        'Ų',
        'У',
        'Ǔ',
        'Ǖ',
        'Ǘ',
        'Ǚ',
        'Ǜ',
      ],
      'V' => ['В'],
      'W' => ['Ω', 'Ώ', 'Ŵ'],
      'X' => ['Χ', 'Ξ'],
      'Y' => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
      'Z' => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
      'AE' => ['Ä', 'Æ', 'Ǽ'],
      'CH' => ['Ч'],
      'DJ' => ['Ђ'],
      'DZ' => ['Џ'],
      'GX' => ['Ĝ'],
      'HX' => ['Ĥ'],
      'IJ' => ['Ĳ'],
      'JX' => ['Ĵ'],
      'KH' => ['Х'],
      'LJ' => ['Љ'],
      'NJ' => ['Њ'],
      'OE' => ['Ö', 'Œ'],
      'PS' => ['Ψ'],
      'SH' => ['Ш'],
      'SHCH' => ['Щ'],
      'SS' => ['ẞ'],
      'TH' => ['Þ'],
      'TS' => ['Ц'],
      'UE' => ['Ü'],
      'YA' => ['Я'],
      'YU' => ['Ю'],
      'ZH' => ['Ж'],
      ' ' => [
        "\xC2\xA0",
        "\xE2\x80\x80",
        "\xE2\x80\x81",
        "\xE2\x80\x82",
        "\xE2\x80\x83",
        "\xE2\x80\x84",
        "\xE2\x80\x85",
        "\xE2\x80\x86",
        "\xE2\x80\x87",
        "\xE2\x80\x88",
        "\xE2\x80\x89",
        "\xE2\x80\x8A",
        "\xE2\x80\xAF",
        "\xE2\x81\x9F",
        "\xE3\x80\x80",
      ],
    ];
  }
}

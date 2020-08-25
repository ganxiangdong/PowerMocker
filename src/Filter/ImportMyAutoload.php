<?php
namespace PowerMocker\Filter;

use PowerMocker\Transform;

/**
 * Class ImportFilter
 * @package PowerMocker
 */
class ImportMyAutoload extends \php_user_filter
{
    /**
     * 过滤器名称
     */
    const NAME = 'powerMock.ImportFilter';

    /**
     * 文件内容++
     * @var string
     */
    private $data = '';

    /**
     * 过滤
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $this->data .= $bucket->data;
        }
        if ($closing || feof($this->stream)) {
            $consumed = strlen($this->data);

//            $metadata = stream_get_meta_data($this->stream);
//            $fileNameIdentity = $metadata['uri'].md5($this->data);
//            $syntaxTree = ReflectionEngine::parseFile($fileNameIdentity, $this->data);
//
//            $meta = stream_get_meta_data($this->stream);

            $code = $this->modifyCode();

            $bucket = stream_bucket_new($this->stream, $code);
            stream_bucket_append($out, $bucket);

            return PSFS_PASS_ON;
        }

        return PSFS_FEED_ME;
    }

    /**
     * 修改代码
     * @return string
     */
    private function modifyCode()
    {
        $code = (new Transform())->run($this->data);
        return $code;
    }
}

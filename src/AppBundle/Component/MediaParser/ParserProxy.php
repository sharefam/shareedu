<?php

namespace AppBundle\Component\MediaParser;

use Topxia\Service\Common\ServiceKernel;

class ParserProxy
{
    private $mockedParser = null;

    public function parseItem($url)
    {
        $parsers = array('YoukuVideo', 'QQVideo', 'NeteaseOpenCourse');

        $kernel = ServiceKernel::instance();
        $extras = array();

        if ($kernel->hasParameter('media_parser')) {
            $extras = $kernel->getParameter('media_parser');
        }

        if (isset($extras['item'])) {
            $extrasParsers = $extras['item'];

            foreach ($extrasParsers as $extrasParser) {
                $class = $extrasParser['class'];
                $parser = new $class();

                if (!$parser->detect($url)) {
                    continue;
                }

                return $parser->parse($url);
            }
        }

        foreach ($parsers as $parserName) {
            $parser = $this->createParser("{$parserName}ItemParser");

            if (!$parser->detect($url)) {
                continue;
            }

            return $parser->parse($url);
        }

        throw $this->createParserNotFoundException();
    }

    public function parseAlbum($url)
    {
        $parsers = array('YoukuVideo', 'QQVideo', 'NeteaseOpenCourse', 'SinaOpenCourse');

        foreach ($parsers as $parserName) {
            $class = __NAMESPACE__."\\AlbumParser\\{$parserName}AlbumParser";
            $parser = new $class();

            if (!$parser->detect($url)) {
                continue;
            }

            return $parser->parse($url);
        }

        throw $this->createParserNotFoundException();
    }

    public function prepareMediaUri($video)
    {
        if ('self' != $video['mediaSource']) {
            if ('youku' == $video['mediaSource']) {
                $parser = $this->createParser('YoukuVideoItemParser');
            } elseif ('NeteaseOpenCourse' == $video['mediaSource']) {
                $parser = $this->createParser('NeteaseOpenCourseItemParser');
            } elseif ('qqvideo' == $video['mediaSource']) {
                $parser = $this->createParser('QQVideoItemParser');
            } else {
                throw $this->createParserNotFoundException();
            }

            return $parser->prepareMediaUri($video);
        }

        return $video;
    }

    public function prepareYoukuMediaUri($video)
    {
        if ('youku' == $video['mediaSource']) {
            return $this->prepareMediaUri($video);
        }

        return $video;
    }

    public function prepareMediaUriForMobile($video, $httpSchema = '')
    {
        if ('youku' == $video['mediaSource']) {
            $parser = $this->createParser('YoukuVideoItemParser');
        } elseif ('qq' == $video['mediaSource']) {
            $parser = $this->createParser('QQVideoItemParser');
        } else {
            return $video;
        }

        return $parser->prepareMediaUriForMobile($video, $httpSchema);
    }

    private function createParser($parserName)
    {
        if (empty($this->mockedParser)) {
            $class = __NAMESPACE__.'\\ItemParser\\'.$parserName;

            return new $class();
        }

        return $this->mockedParser;
    }

    protected function createParserNotFoundException($message = '')
    {
        return new ParserNotFoundException($message);
    }
}

<?php

declare(strict_types=1);

namespace Scriptmancer\Http\Stream;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class Stream
{
    private StreamInterface $stream;

    /**
     * Create a new stream
     *
     * @param string|resource|StreamInterface $content Stream content
     */
    public function __construct(mixed $content = '')
    {
        $this->stream = Utils::streamFor($content);
    }

    /**
     * Create a stream from a file
     *
     * @param string $filename File path
     * @param string $mode File mode
     * @return self
     */
    public static function fromFile(string $filename, string $mode = 'r'): self
    {
        return new self(Utils::tryFopen($filename, $mode));
    }

    /**
     * Create a stream from a string
     *
     * @param string $content String content
     * @return self
     */
    public static function fromString(string $content): self
    {
        return new self($content);
    }

    /**
     * Get the size of the stream
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    /**
     * Returns whether or not the stream is readable
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    /**
     * Returns whether or not the stream is writable
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    /**
     * Returns whether or not the stream is seekable
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    /**
     * Read data from the stream
     *
     * @param int $length Bytes to read
     * @return string
     */
    public function read(int $length): string
    {
        return $this->stream->read($length);
    }

    /**
     * Write data to the stream
     *
     * @param string $string Data to write
     * @return int
     */
    public function write(string $string): int
    {
        return $this->stream->write($string);
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->stream->getContents();
    }

    /**
     * Cast the stream to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->stream;
    }

    /**
     * Closes the stream and any underlying resources
     *
     * @return void
     */
    public function close(): void
    {
        $this->stream->close();
    }

    /**
     * Get the underlying PSR-7 stream
     *
     * @return StreamInterface
     */
    public function getPsrStream(): StreamInterface
    {
        return $this->stream;
    }
} 
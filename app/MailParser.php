<?php namespace ChaoticWave\LeakyThoughts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use PhpMimeMailParser\Parser;

class MailParser extends Parser implements Arrayable, Jsonable
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var string The current message mime type
     */
    protected $mimeType;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function toArray()
    {
        return $this->getParts();
    }

    /** @inheritdoc */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Explodes the $parts of a message into individual related components and returns them in an array
     *
     * @param bool $includeBase If true, the base elements of the $parts array will be included. These are mainly positions within the text file and not
     *                          useful for search
     *
     * @return array|bool FALSE on error
     */
    public function explodeParts($includeBase = true)
    {
        $_headers = $this->getHeaders();
        $_parts = $this->getParts();

        $_base = $includeBase ? reset($_parts) : [];

        //  Lose the positionals
        array_forget($_base, ['starting-pos-body', 'ending-pos', 'ending-pos-body', 'starting-pos']);

        $_base['date'] = array_get($_headers, 'date');
        $_base['message_id'] = array_get($_headers, 'message-id');
        $_base['spam_score'] = array_get($_headers, 'x-spam');
        $_base['source_ip'] = $_sourceIp = str_replace(['[', ']'], null, array_get($_headers, 'x-source-ip'));
        $_base['source_hostname'] = empty($_sourceIp) ? null : gethostbyaddr($_sourceIp);

        return array_merge($_base,
            [
                'headers'     => $_headers,
                'addresses'   => [
                    'from' => $this->getAddresses('from'),
                    'to'   => $this->getAddresses('to'),
                    'cc'   => $this->getAddresses('cc'),
                    'bcc'  => $this->getAddresses('bcc'),
                ],
                'subject'     => $this->getHeader('subject'),
                // 'body'        => [
                //     'text'         => $this->getMessageBody('text'),
                //     'html'         => $this->getMessageBody('html'),
                //     'htmlEmbedded' => $this->getMessageBody('htmlEmbedded'),
                // ],
                'body_text'   => $this->getMessageBody('text'),
                'attachments' => $this->getAttachments(),
            ]);
    }

    /**
     * @return array
     */
    public static function getMapping()
    {
        return [
            'properties' => [
                'body_text' => ['type' => 'string', 'analyzer' => 'english'],
            ],
        ];
    }
}
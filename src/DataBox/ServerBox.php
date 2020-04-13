<?php
namespace Mtchabok\Request\DataBox;

/**
 * Class ServerBox
 * @package Mtchabok\Request
 *
 * @property 		string		AUTH_TYPE
 * @property 		string		PHP_SELF
 * @property 		string		PHP_AUTH_DIGEST
 * @property 		string		PHP_AUTH_USER
 * @property 		string		PHP_AUTH_PW
 * @property 		string		PATH_TRANSLATED
 * @property 		string		PATH_INFO
 * @property 		string		ORIG_PATH_INFO
 * @property 		string		QUERY_STRING
 * @property 		string		SERVER_ADDR
 * @property 		string		SERVER_ADMIN
 * @property 		string		SERVER_NAME
 * @property 		string		SERVER_SOFTWARE
 * @property 		string		SERVER_PROTOCOL
 * @property 		string		SERVER_PORT
 * @property 		string		SERVER_SIGNATURE
 * @property 		string		REMOTE_ADDR
 * @property 		string		REMOTE_HOST
 * @property 		string		REMOTE_PORT
 * @property 		string		REMOTE_USER
 * @property 		string		REDIRECT_REMOTE_USER
 * @property 		string		DOCUMENT_ROOT
 * @property 		string		HTTP_ACCEPT
 * @property 		string		HTTP_ACCEPT_CHARSET
 * @property 		string		HTTP_ACCEPT_ENCODING
 * @property 		string		HTTP_ACCEPT_LANGUAGE
 * @property 		string		HTTP_CONNECTION
 * @property 		string		HTTP_HOST
 * @property 		string		HTTP_REFERER
 * @property 		string		HTTP_USER_AGENT
 * @property 		string		SCRIPT_FILENAME
 * @property 		string		SCRIPT_NAME
 * @property 		string		REQUEST_METHOD
 * @property 		string		REQUEST_URI
 * @property 		string		REQUEST_URL
 * @property 		int			REQUEST_TIME
 * @property 		float		REQUEST_TIME_FLOAT
 *
 * @property 		array		argv
 * @property 		int			argc
 */
class ServerBox extends ParameterBox
{
	public function __construct(array $options = null)
	{
		if(!is_array($options)) $options = [];
		if(empty($options['localOnly']) && !array_key_exists('parent', $options))
			$options['parent'] = &$_SERVER;

		parent::__construct($options);

		// create REQUEST_URL property
		if($this->exist('HTTP_HOST') || $this->exist('SERVER_NAME')){
			$url = $this->getString('REQUEST_SCHEME', ('http' . (!empty($this['HTTPS']) ? 's' : '')) ) .'://' ;
			if(!$this->isEmpty('SERVER_NAME')) {
				$url .= $this->getString('SERVER_NAME');
				if(!$this->isEmpty('SERVER_PORT') && 80!=$this->getNumber('SERVER_PORT',80))
					$url.= ":{$this->get('SERVER_PORT')}";
			}elseif (!$this->isEmpty('HTTP_HOST'))
				$url .= $this->getString('HTTP_HOST');
			$url .= substr((string) $this['REQUEST_URI'], 0, 1) == '/'
				? (string) $this['REQUEST_URI'] : "/{$this['REQUEST_URI']}";
			$this->set('REQUEST_URL', $url);
		}

	}
}

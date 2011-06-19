<?php

abstract class Lily_Xmlrpc_Adapter_Abstract
{

	abstract public function sendRequest(XMLRPC_Request& $request);
	
	abstract public function sendResponse(XMLRPC_Response& $request);

}

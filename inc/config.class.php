<?php
class PluginAzureConfig extends CommonDBTM
{
    /**
     * ID de l'annuaire (locataire)
     */
    const TENANT = 'YOUR-TENANT-HERE';
    /**
     * Mode développement si true
     */
    const DEV = FALSE;

    /**
     * ID d'application (client)
     */
    private $_client_id = 'YOUR-CLIENID-HERE';
    /**
     * Scope
     */
    private $_scope = 'openid user.read email profile';
    /**
     * Url d'autorisation, permet d'obtenir le token_id
     */
    private $_authorize_url = 'https://login.microsoftonline.com/' . self::TENANT . '/oauth2/v2.0/authorize';
    /**
     * Url pour la vérification du token_id
     */
    private $_string_microsoftPublicKeyURL = 'https://login.microsoftonline.com/' . self::TENANT . '/discovery/v2.0/keys';
    /**
     * Url pour la vérification du token_id décodé
     */
    private $_string_microsoftPublicURL = 'https://login.microsoftonline.com/' . self::TENANT . '/v2.0';

    public $client_id;
    public $scope;
    public $authorize_url;
    public $redirect_url;
    public $string_microsoftPublicKeyURL;
    public $string_microsoftPublicURL;
    


    function __construct() {
        if(self::DEV){
            $this->redirect_url = 'http(s)://(your-domaine)/plugins/azure/front/callback.php';
        }else{
            $this->redirect_url = 'http(s)://(your-domaine)/plugins/azure/front/callback.php';
        }

        $this->client_id = $this->_client_id;
        $this->scope = $this->_scope;
        $this->authorize_url = $this->_authorize_url;
        $this->string_microsoftPublicKeyURL = $this->_string_microsoftPublicKeyURL;
        $this->string_microsoftPublicURL = $this->_string_microsoftPublicURL;
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use DOMDocument;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register any API-specific bindings
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Add XML response macro to match your existing API format
        Response::macro('xml', function ($data, $status = 200, array $headers = []) {
            if ($data instanceof \SimpleXMLElement) {
                $content = $data->asXML();
            } else {
                $content = $this->arrayToXml($data);
            }

            $headers['Content-Type'] = 'application/xml';
            return response($content, $status, $headers);
        });

        // Add custom validation rules that match your API requirements
        Validator::extend('valid_token', function ($attribute, $value, $parameters, $validator) {
            return \App\User::where('token', $value)->exists();
        });

        // Add other API-specific validations as needed
        Validator::extend('valid_player_guid', function ($attribute, $value, $parameters, $validator) {
            return \App\User::where('id', $value)->exists();
        });
    }

    /**
     * Convert array to XML (matches your existing API format)
     *
     * @param array $data
     * @param string $rootNode
     * @return string
     */
    protected function arrayToXml(array $data, $rootNode = 'Response')
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        
        $root = $doc->createElement($rootNode);
        $doc->appendChild($root);
        
        $this->addArrayToXml($doc, $root, $data);
        
        return $doc->saveXML();
    }

    /**
     * Recursively add array data to XML document
     *
     * @param DOMDocument $doc
     * @param \DOMElement $node
     * @param array $data
     */
    protected function addArrayToXml(DOMDocument $doc, \DOMElement $node, array $data)
    {
        foreach ($data as $key => $value) {
            // Handle numeric keys (for lists)
            if (is_numeric($key)) {
                $key = "item";
            }

            $child = $doc->createElement($key);
            
            if (is_array($value)) {
                $this->addArrayToXml($doc, $child, $value);
            } else {
                $child->appendChild($doc->createTextNode($value));
            }
            
            $node->appendChild($child);
        }
    }
}
# Autométrica PHP Client
Una implementación no oficial del Webservice para Autométrica.

## Notas
Esta librería NO está relacionada con Autométrica de ninguna manera y/o forma. Es simplemente un trabajo derivado para
poder utilizar el Webservice de Autométrica de una manera práctica y sencilla mediante modelos POPO (Plain Old PHP 
Objects).

## Instalación
### Dependencias
Esta librería depende de un cliente HTTP que implemente la interfaz PSR-18. Puede instalar el cliente de referencia
para esta implementación de la siguiente manera:

    composer require symfony/http-client

### Librería
La instalación del cliente es simple, solo debes ejecutar el siguiente comando:

    composer require instacar/autometrica-webservice-client

## Uso
Para usar el cliente puedes crear una instancia por defecto que se encargará de crear el cliente HTTP PSR-18. El cliente 
tiene un método por cada punto final del Webservice de Autometrica. Por ejemplo, para solicitar el catálogo de vehículos:

~~~php
use Instacar\AutometricaWebserviceClient\AutometricaClient;

$cliente = AutometricaClient::createDefault($username, $password);
$catalogo = $cliente->getCatalog();
~~~

Cada entidad de la Webservice está modelada con una clase PHP que tiene getters para cada una de las propiedades, con el
fin de proporcionar ayuda a los IDEs y autocompletar mejor, además de proporcionar tipos estrictos a cada una de las 
propiedades. Siguiendo con el ejemplo anterior:

~~~php
foreach ($catalogo as $vehiculo) {
    echo $vehiculo->getBrand(); // La marca del vehículo
    echo $vehiculo->getModel(); // El modelo del vehículo
    echo $vehiculo->getYear();  // EL año del vehículo
    echo $vehiculo->getTrim();  // La versión del vehículo
}
~~~

Si desea ver los métodos y modelos implementados, por favor, consulte la documentación.

### Avanzado
Si deseas utilizar tus propias implementaciones del cliente HTTP PSR-18, puedes instanciar directamente el 
``AutometricaClient`` con el cliente HTTP.

~~~php
use Instacar\AutometricaWebserviceClient\AutometricaClient;

$cliente = new AutometricaClient($clienteHttp, $username, $password);
~~~

## Advertencias
- Ciertas líneas que no fueron comercializadas en México en años específicos son devueltas por el Webservice con la
versión ***"NOTA: En este año, el vehículo no se comercializó en México"***. Si se intenta consultar sus precios el 
sistema devolverá una colección vacía.

## Licencia
Esta librería utiliza la licencia Lesser General Public Licence Version 3 (LGPLv3). Puede consultarla en el archivo
[LICENSE](LICENSE).

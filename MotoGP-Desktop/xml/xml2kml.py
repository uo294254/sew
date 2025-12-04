import xml.etree.ElementTree as ET

class Kml(object):
    """
    Genera archivo KML con puntos y líneas
    """
    def __init__(self):
        """
        Crea el elemento raíz y el espacio de nombres
        """
        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz,'Document')

    def addPlacemark(self,nombre,descripcion,long,lat,alt, modoAltitud):
        """
        Añade un elemento <Placemark> con puntos <Point>
        """
        pm = ET.SubElement(self.doc,'Placemark')
        ET.SubElement(pm,'name').text = nombre
        ET.SubElement(pm,'description').text = descripcion
        punto = ET.SubElement(pm,'Point')
        ET.SubElement(punto,'coordinates').text = '{},{},{}'.format(long,lat,alt)
        ET.SubElement(punto,'altitudeMode').text = modoAltitud

    def addLineString(self,nombre,extrude,tesela, listaCoordenadas, modoAltitud, color, ancho):
        """
        Añade un elemento <Placemark> con líneas <LineString>
        """
        ET.SubElement(self.doc,'name').text = nombre
        pm = ET.SubElement(self.doc,'Placemark')
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls,'extrude').text = extrude
        ET.SubElement(ls,'tessellation').text = tesela
        ET.SubElement(ls,'coordinates').text = listaCoordenadas
        ET.SubElement(ls,'altitudeMode').text = modoAltitud

        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement (linea, 'color').text = color
        ET.SubElement (linea, 'width').text = ancho

    def escribir(self,nombreArchivoKML):
        """
        Escribe el archivo KML con declaración y codificación
        """
        arbol = ET.ElementTree(self.raiz)
        """
        Introduce indentacióon y saltos de línea
        para generar XML en modo texto
        """
        ET.indent(arbol)
        arbol.write(nombreArchivoKML, encoding='utf-8', xml_declaration=True)

def main():

    tree = ET.parse('circuitoEsquema.xml')
    root = tree.getroot()
    ns = {'ns': 'http://www.uniovi.es'}

    nuevoKML = Kml()

    origen = root.find('ns:punto_origen', ns)
    long_origen = float(origen.find('ns:longitud_geo', ns).text)
    lat_origen = float(origen.find('ns:latitud_geo', ns).text)
    alt_origen = float(origen.find('ns:altitud_geo', ns).text)

    nuevoKML.addPlacemark('Punto Origen',
                        'Origen del circuito',
                        long_origen, lat_origen, alt_origen,
                        'relativeToGround')

    coordenadas = []
    coordenadas.append(f'{long_origen},{lat_origen},{alt_origen}')

    cont = 1
    for tramo in root.findall('ns:tramos/ns:tramo', ns):
        lon = float(tramo.find('ns:longitud_geo', ns).text)
        lat = float(tramo.find('ns:latitud_geo', ns).text)
        alt = float(tramo.find('ns:altitud_geo', ns).text)
        coordenadas.append(f'{lon},{lat},{alt}')

        distancia = tramo.find('ns:distancia', ns).text

        nuevoKML.addPlacemark(f"Tramo{cont}",
                        f"Distancia de tramo: {distancia}m",
                        lon, lat, alt,
                        'relativeToGround')
        cont += 1

    coordenadas.append(f'{long_origen},{lat_origen},{alt_origen}')

    listaCoordenadas = '\n'.join(coordenadas)
    nuevoKML.addLineString('Circuito Completo',
                        '1', '1',
                        listaCoordenadas,
                        'relativeToGround',
                        '#ff0000ff',
                        '5')

    nuevoKML.escribir('circuito.kml')
    print("Archivo KML generado: circuito.kml")


if __name__ == "__main__":
    main()

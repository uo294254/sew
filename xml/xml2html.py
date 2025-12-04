import xml.etree.ElementTree as ET

class Html:
    """
    Genera archivos HTML con información de un circuito
    @version 1.0 22/Octubre/2025
    @author: Mario Trelles Riestra. Universidad de Oviedo
    """
    def __init__(self, titulo="Información del Circuito"):
        """
        Crea el elemento raíz <html> y el <head> con enlace al CSS
        """
        self.root = ET.Element('html', lang="es")
        head = ET.SubElement(self.root, 'head')
        ET.SubElement(head, 'meta', charset="UTF-8")
        ET.SubElement(head, 'title').text = titulo
        ET.SubElement(head, 'link', rel="stylesheet", href="../estilo/estilo.css")
        self.body = ET.SubElement(self.root, 'body')
        ET.SubElement(self.body, 'h1').text = titulo

    def addParagraph(self, texto):
        """
        Añade un párrafo <p> al body
        """
        ET.SubElement(self.body, 'p').text = texto

    def addTable(self, filas):
        """
        Añade una tabla <table> con contenido en filas (lista de listas)
        """
        table = ET.SubElement(self.body, 'table')
        for fila in filas:
            tr = ET.SubElement(table, 'tr')
            for celda in fila:
                ET.SubElement(tr, 'td').text = str(celda)

    def escribir(self, nombreArchivoHTML):
        """
        Escribe el archivo HTML con indentación
        """
        arbol = ET.ElementTree(self.root)
        ET.indent(arbol)
        with open(nombreArchivoHTML, 'w', encoding='utf-8') as f:
            f.write("<!DOCTYPE html>\n")
            arbol.write(f, encoding='unicode', method='html')
        print(f"Archivo HTML creado: {nombreArchivoHTML}")

def main():
    tree = ET.parse('circuitoEsquema.xml')
    root = tree.getroot()
    ns = {'ns': 'http://www.uniovi.es'}

    html = Html("Información del Circuito")

    nombre = root.find('ns:nombre', ns).text
    longitud = root.find('ns:longitud', ns).text
    unidades_longitud = root.find('ns:longitud', ns).attrib.get('unidades')
    anchura = root.find('ns:anchura', ns).text
    unidades_anchura = root.find('ns:anchura', ns).attrib.get('unidades')
    fecha = root.find('ns:fecha', ns).text
    hora = root.find('ns:hora', ns).text
    vueltas = root.find('ns:vueltas', ns).text
    localidad = root.find('ns:localidad', ns).text
    pais = root.find('ns:pais', ns).text
    patrocinador = root.find('ns:patrocinador', ns).text

    html.addTable([
        ["Nombre", nombre],
        ["Longitud", f"{longitud} {unidades_longitud}"],
        ["Anchura", f"{anchura} {unidades_anchura}"],
        ["Fecha", fecha],
        ["Hora", hora],
        ["Vueltas", vueltas],
        ["Localidad", localidad],
        ["País", pais],
        ["Patrocinador", patrocinador]
    ])

    referencias = root.findall('ns:referencias/ns:referencia', ns)
    html.addParagraph("Referencias:")
    for r in referencias:
        html.addParagraph(f"- {r.text}")

    fotos = root.findall('ns:fotos/ns:foto', ns)
    for foto in fotos:
        img = ET.SubElement(html.body, 'img')
        img.set('src', foto.attrib['archivo'])
        img.set('alt', foto.text)

    videos = root.findall('ns:videos/ns:video', ns)
    for video in videos:
        vid = ET.SubElement(html.body, 'video', controls="controls")
        source = ET.SubElement(vid, 'source', src=video.attrib['archivo'], type="video/mp4")

    resultado = root.find('ns:resultado', ns)
    vencedor = resultado.find('ns:vencedor', ns).text
    tiempo = resultado.find('ns:tiempo', ns).text
    html.addParagraph(f"Vencedor: {vencedor}, Tiempo: {tiempo}")

    clasificacion = root.find('ns:clasificacion', ns)
    piloto1 = clasificacion.find('ns:piloto1', ns).text
    piloto2 = clasificacion.find('ns:piloto2', ns).text
    piloto3 = clasificacion.find('ns:piloto3', ns).text
    html.addParagraph(f"Clasificación: {piloto1}, {piloto2}, {piloto3}")

    html.escribir("InfoCircuito.html")

if __name__ == "__main__":
    main()

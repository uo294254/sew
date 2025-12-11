import xml.etree.ElementTree as ET

class Html:
    """
    Genera archivos HTML con información de un circuito
    @version 1.0 22/Octubre/2025
    @author: Mario Trelles Riestra. Universidad de Oviedo
    """
    def __init__(self, titulo="Información del Circuito"):
        self.root = ET.Element("html", lang="es")

        head = ET.SubElement(self.root, "head")
        ET.SubElement(head, "meta", charset="UTF-8")
        ET.SubElement(head, "title").text = titulo
        ET.SubElement(head, "link", rel="stylesheet", href="estilo/estilo.css")

        self.body = ET.SubElement(self.root, "body")
        ET.SubElement(self.body, "h1").text = titulo

    def newSection(self, titulo_h3=None):
        """
        Crea una nueva sección y devuelve el elemento
        """
        section = ET.SubElement(self.body, "section")
        if titulo_h3:
            ET.SubElement(section, "h3").text = titulo_h3
        return section

    def addListTo(self, parent, items):
        ul = ET.SubElement(parent, "ul")
        for item in items:
            ET.SubElement(ul, "li").text = item

    def addLinkTo(self, parent, texto, url):
        a = ET.SubElement(parent, "a", href=url, target="_blank")
        a.text = texto

    def addImageTo(self, parent, archivo, alt="Imagen"):
        ET.SubElement(parent, "img", src=archivo, alt=alt)

    def addVideoTo(self, parent, archivo):
        video = ET.SubElement(parent, "video", controls="controls")
        ET.SubElement(video, "source", src=archivo, type="video/mp4")

    def addParagraphTo(self, parent, texto):
        ET.SubElement(parent, "p").text = texto

    def escribir(self, nombreArchivoHTML):
        arbol = ET.ElementTree(self.root)
        ET.indent(arbol)
        with open(nombreArchivoHTML, "w", encoding="utf-8") as f:
            f.write("<!DOCTYPE html>\n")
            arbol.write(f, encoding="unicode", method="html")
        print("Archivo HTML creado:", nombreArchivoHTML)

    def formatear_duracion(self, iso):
        partes = iso.split("M")
        minutos = partes[0]
        minutos = minutos[2:]
        segundos = partes[1]
        segundos = segundos[:5]
        return f"{minutos} minutos y {segundos} segundos"

def main():
    tree = ET.parse('circuitoEsquema.xml')
    root = tree.getroot()
    ns = {'ns': 'http://www.uniovi.es'}

    html = Html("Información del Circuito")

    nombre = root.find(f'.//{{{ns["ns"]}}}nombre').text

    longitud_elem = root.find(f'.//{{{ns["ns"]}}}longitud')
    longitud = longitud_elem.text
    unidades_longitud = longitud_elem.get('unidades')

    anchura_elem = root.find(f'.//{{{ns["ns"]}}}anchura')
    anchura = anchura_elem.text
    unidades_anchura = anchura_elem.get('unidades')

    fecha = root.find(f'.//{{{ns["ns"]}}}fecha').text
    hora = root.find(f'.//{{{ns["ns"]}}}hora').text
    vueltas = root.find(f'.//{{{ns["ns"]}}}vueltas').text
    localidad = root.find(f'.//{{{ns["ns"]}}}localidad').text
    pais = root.find(f'.//{{{ns["ns"]}}}pais').text
    patrocinador = root.find(f'.//{{{ns["ns"]}}}patrocinador').text

    sec_datos = html.newSection("Datos del Circuito")
    html.addListTo(sec_datos, [
        f"Nombre: {nombre}",
        f"Longitud: {longitud} {unidades_longitud}",
        f"Anchura: {anchura} {unidades_anchura}",
        f"Fecha: {fecha}",
        f"Hora: {hora}",
        f"Vueltas: {vueltas}",
        f"Localidad: {localidad}",
        f"País: {pais}",
        f"Patrocinador: {patrocinador}"
    ])

    referencias = root.findall(f'.//{{{ns["ns"]}}}referencias/{{{ns["ns"]}}}referencia')
    if referencias:
        sec_ref = html.newSection("Referencias")
        for r in referencias:
            html.addLinkTo(sec_ref, r.text, r.text)

    fotos = root.findall(f'.//{{{ns["ns"]}}}fotos/{{{ns["ns"]}}}foto')
    if fotos:
        sec_fotos = html.newSection("Fotos")
        for foto in fotos:
            html.addImageTo(
                sec_fotos,foto.get('archivo'),
                foto.text if foto.text else "Foto del circuito"
            )

    videos = root.findall(f'.//{{{ns["ns"]}}}videos/{{{ns["ns"]}}}video')
    if videos:
        sec_videos = html.newSection("Videos")
        for video in videos:
            html.addVideoTo(sec_videos, video.get('archivo'))

    resultado = root.find(f'.//{{{ns["ns"]}}}resultado')
    if resultado is not None:
        sec_result = html.newSection("Resultado")
        vencedor = resultado.find(f'{{{ns["ns"]}}}vencedor').text
        tiempo_iso = resultado.find(f'{{{ns["ns"]}}}tiempo').text
        html.addParagraphTo(sec_result, f"Vencedor: {vencedor}")
        tiempo_formateado = html.formatear_duracion(tiempo_iso)
        html.addParagraphTo(sec_result, f"Tiempo: {tiempo_formateado}")

    clasificacion = root.find(f'.//{{{ns["ns"]}}}clasificacion')
    if clasificacion is not None:
        piloto1 = clasificacion.find(f'{{{ns["ns"]}}}piloto1').text
        piloto2 = clasificacion.find(f'{{{ns["ns"]}}}piloto2').text
        piloto3 = clasificacion.find(f'{{{ns["ns"]}}}piloto3').text

        sec_clas = html.newSection("Clasificación")
        html.addListTo(sec_clas, [
            f"1º: {piloto1}",
            f"2º: {piloto2}",
            f"3º: {piloto3}"
        ])

    html.escribir("InfoCircuito.html")

if __name__ == "__main__":
    main()
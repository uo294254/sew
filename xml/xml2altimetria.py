# -*- coding: utf-8 -*-
"""
Crea archivos SVG con rectángulos, círculos, líneas, polilíneas y texto

@version 1.0 22/Octubre/2025
@author: Mario Trelles Riestra. Universidad de Oviedo
"""

import xml.etree.ElementTree as ET

class Svg(object):
    """
    Genera archivos SVG con rectángulos, círculos, líneas, polilíneas y texto
    @version 1.0 22/Octubre/2025
    @author: Mario Trelles Riestra. Universidad de Oviedo
    """
    def __init__(self):
        """
        Crea el elemento raíz, el espacio de nombres y la versión
        """
        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg", version="1.1")

    def addRect(self, x, y, width, height, fill, strokeWidth, stroke):
        """
        Añade un elemento rect
        """
        ET.SubElement(self.raiz, 'rect',
                      x=str(x),
                      y=str(y),
                      width=str(width),
                      height=str(height),
                      fill=fill,
                      **{'stroke-width': str(strokeWidth)},
                      stroke=stroke)

    def addCircle(self, cx, cy, r, fill):
        """
        Añade un elemento circle
        """
        ET.SubElement(self.raiz, 'circle',
                      cx=cx,
                      cy=cy,
                      r=r,
                      fill=fill)

    def addLine(self, x1, y1, x2, y2, stroke, strokeWidth):
        """
        Añade un elemento line
        """
        ET.SubElement(self.raiz, 'line',
                  x1=str(x1), y1=str(y1),
                  x2=str(x2), y2=str(y2),
                  stroke=stroke,
                  **{'stroke-width': str(strokeWidth)})

    def addPolyline(self, points, stroke, strokeWidth, fill):
        """
        Añade un elemento polyline
        """
        ET.SubElement(self.raiz, 'polyline',
                      points=points,
                      stroke=stroke,
                      **{'stroke-width': strokeWidth},
                      fill=fill)

    def addText(self, texto, x, y, fontSize=14, anchor="middle"):
        """
        Añade un elemento texto
        """
        ET.SubElement(self.raiz, 'text',
                      x=str(x),
                      y=str(y),
                      **{"text-anchor": anchor,
                         "font-size": str(fontSize)}
                      ).text = texto

    def escribir(self, nombreArchivoSVG):
        """
        Escribe el archivo SVG con declaración y codificación
        """
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoSVG,
                    encoding='utf-8',
                    xml_declaration=True)

    def ver(self):
        """
        Muestra el archivo SVG. Se utiliza para depurar
        """
        print("\nElemento raiz = ", self.raiz.tag)

        if self.raiz.text != None:
            print("Contenido = ", self.raiz.text.strip('\n'))
        else:
            print("Contenido = ", self.raiz.text)

        print("Atributos = ", self.raiz.attrib)

        for hijo in self.raiz.findall('./'):
            print("\nElemento = ", hijo.tag)
            if hijo.text != None:
                print("Contenido = ", hijo.text.strip('\n'))
            else:
                print("Contenido = ", hijo.text)
            print("Atributos = ", hijo.attrib)

def main():
    print(Svg.__doc__)

    tree = ET.parse('circuitoEsquema.xml')
    root = tree.getroot()
    ns = '{http://www.uniovi.es}'

    ancho_svg = 500
    alto_svg = 350
    margen = 50
    desplazamiento_x = 50

    nombreSVG = "altimetria.svg"
    nuevoSVG = Svg()
    nuevoSVG.addText("Altimetría del circuito", ancho_svg/2 + desplazamiento_x, 30, fontSize=20)

    nuevoSVG.addRect(
        x=str(margen + desplazamiento_x),
        y=str(margen),
        width=str(ancho_svg - 2*margen),
        height=str(alto_svg - 2*margen),
        fill="none",
        strokeWidth="2",
        stroke="black"
    )

    origen = root.find(f'.//{ns}punto_origen')
    alt_origen = float(origen.find(f'{ns}altitud_geo').text)

    altitudes = [alt_origen]
    distancias = [0.0]

    tramos = root.findall(f'.//{ns}tramos/{ns}tramo')

    for tramo in tramos:
        dist = float(tramo.find(f'{ns}distancia').text)
        alt = float(tramo.find(f'{ns}altitud_geo').text)
        distancias.append(distancias[-1] + dist)
        altitudes.append(alt)

    max_dist = max(distancias)
    min_alt = min(altitudes)
    max_alt = max(altitudes)

    nuevoSVG.addText("Distancia (m)", ancho_svg/2 + desplazamiento_x, alto_svg - 10)
    nuevoSVG.addText("Altitud (m)",
                     desplazamiento_x,
                     alto_svg/2,
                     fontSize=14,
                     anchor="middle")

    for i in range(6):
        x = margen + (i * (ancho_svg - 2*margen) / 5) + desplazamiento_x
        dist_label = round(max_dist * i / 5)

        nuevoSVG.addText(str(dist_label),
                         x,
                         alto_svg - margen + 20,
                         fontSize=12)

        nuevoSVG.addLine(x,
                         alto_svg - margen,
                         x,
                         margen,
                         stroke="lightgray",
                         strokeWidth=1)

    for i in range(6):
        y = alto_svg - margen - (i * (alto_svg - 2*margen) / 5)
        alt_label = round(min_alt + (max_alt - min_alt) * i / 5)

        nuevoSVG.addText(str(alt_label),
                         margen - 25 + desplazamiento_x,
                         y + 5,
                         fontSize=12,
                         anchor="end")

        nuevoSVG.addLine(margen + desplazamiento_x,
                         y,
                         ancho_svg - margen + desplazamiento_x,
                         y,
                         stroke="lightgray",
                         strokeWidth=1)

    puntos_svg = []
    puntos_svg.append(f"{margen + desplazamiento_x},{alto_svg - margen}")

    for i in range(len(distancias)):
        x = margen + distancias[i] / max_dist * (ancho_svg - 2*margen) + desplazamiento_x
        y = alto_svg - margen - (altitudes[i] - min_alt) / (max_alt - min_alt) * (alto_svg - 2*margen)
        puntos_svg.append(f"{x},{y}")

    puntos_svg.append(f"{margen + (ancho_svg - 2*margen) + desplazamiento_x},{alto_svg - margen}")
    puntos_str = ' '.join(puntos_svg)

    nuevoSVG.addPolyline(puntos_str, 'red', '2', 'orange')

    nuevoSVG.ver()
    nuevoSVG.escribir(nombreSVG)
    print("Creado el archivo: ", nombreSVG)

if __name__ == "__main__":
    main()
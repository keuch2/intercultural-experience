"""Genera los gráficos de Google Play a partir de AppStore/. Uso: python3 -P ieapp/store/google-play/generar_graficos.py "$PWD" (desde la raíz)."""
import os
import sys
from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = sys.argv[1]  # raíz del proyecto
EXTRACT = os.path.join(ROOT, 'AppStore/_extract')
ENTREG = os.path.join(ROOT, 'AppStore/Entregables_AppStore')
FRESH = os.path.join(ROOT, 'ieapp/store/screenshots/iphone-6.7')
OUT = os.path.join(ROOT, 'ieapp/store/google-play')
FONT_BOLD = os.path.expanduser('~/Library/Fonts/Gotham-Bold.otf')
FONT_MED = os.path.expanduser('~/Library/Fonts/Gotham-Medium.otf')

W, H = 1440, 2560

SLIDES = [
    ('01_tu-intercambio', 'Tu intercambio,\nen tu bolsillo', 'Todo tu programa IE en una sola app', ('#1f2b44', '#141019'), os.path.join(FRESH, '02-home.png')),
    ('02_descubri-programas', 'Descubrí programas\nen todo el mundo', 'Au Pair, Work & Travel, Study Abroad y más', ('#59509f', '#463f7d'), os.path.join(EXTRACT, 's_08_Layer_8.png')),
    ('03_conoce-cada', 'Conocé cada\nprograma a fondo', 'Destinos, requisitos y beneficios', ('#0d6bc3', '#0a5499'), os.path.join(EXTRACT, 's_03_Layer_3.png')),
    ('04_postulate-en', 'Postulate en\npocos pasos', 'Un proceso guiado, simple y claro', ('#e11929', '#b01420'), os.path.join(EXTRACT, 's_04_Layer_4.png')),
    ('05_segui-tu-proceso', 'Seguí tu proceso\nen tiempo real', 'Siempre sabés cuál es tu próximo paso', ('#dd7713', '#ad5d0f'), os.path.join(EXTRACT, 's_10_Layer_10.png')),
    ('06_subi-tus-documentos', 'Subí tus documentos\ndesde el celular', 'Sin papeles y sin filas', ('#59509f', '#463f7d'), os.path.join(EXTRACT, 's_11_Layer_11.png')),
    ('07_registra-tus-pagos', 'Registrá tus pagos\ny comprobantes', 'Tu historial, ordenado y verificado', ('#0d6bc3', '#0a5499'), os.path.join(EXTRACT, 's_13_Layer_13.png')),
    ('08_tu-experiencia', 'Tu experiencia\nempieza acá', 'IE - intercultural experience', ('#e11929', '#b01420'), os.path.join(EXTRACT, 's_01_Layer_1.png')),
]


def hex2rgb(h):
    h = h.lstrip('#')
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))


def gradient(size, top, bottom):
    w, h = size
    t, b = hex2rgb(top), hex2rgb(bottom)
    col = Image.new('RGB', (1, h))
    for y in range(h):
        k = y / (h - 1)
        col.putpixel((0, y), tuple(round(t[i] + (b[i] - t[i]) * k) for i in range(3)))
    return col.resize((w, h))


def rounded_mask(size, radius):
    m = Image.new('L', size, 0)
    ImageDraw.Draw(m).rounded_rectangle((0, 0, size[0] - 1, size[1] - 1), radius=radius, fill=255)
    return m


def centered_text(draw, text, font, y, fill, spacing):
    for line in text.split('\n'):
        w = draw.textlength(line, font=font)
        draw.text(((W - w) / 2, y), line, font=font, fill=fill)
        y += font.size + spacing
    return y


def screenshot(name, title, subtitle, colors, screen_path):
    canvas = gradient((W, H), *colors)
    draw = ImageDraw.Draw(canvas)
    title_font = ImageFont.truetype(FONT_BOLD, 104)
    sub_font = ImageFont.truetype(FONT_MED, 50)
    y = centered_text(draw, title, title_font, 150, (255, 255, 255), 22)
    centered_text(draw, subtitle, sub_font, y + 30, (255, 255, 255), 10)

    # Pantalla dentro de un teléfono genérico (bisel oscuro, sin notch ni barra de estado de otra plataforma).
    screen = Image.open(screen_path).convert('RGB')
    bezel, radius = 26, 78
    top, bottom_margin = 640, 90
    inner_h = H - top - bottom_margin - 2 * bezel
    inner_w = round(screen.width * inner_h / screen.height)
    screen = screen.resize((inner_w, inner_h), Image.LANCZOS)
    phone_w, phone_h = inner_w + 2 * bezel, inner_h + 2 * bezel
    x = (W - phone_w) // 2

    shadow = Image.new('L', (W, H), 0)
    ImageDraw.Draw(shadow).rounded_rectangle((x, top + 30, x + phone_w, top + phone_h + 30), radius=radius, fill=150)
    shadow = shadow.filter(ImageFilter.GaussianBlur(45))
    canvas.paste(Image.new('RGB', (W, H), (0, 0, 0)), (0, 0), shadow)

    phone = Image.new('RGB', (phone_w, phone_h), (17, 17, 20))
    phone.paste(screen, (bezel, bezel), rounded_mask((inner_w, inner_h), radius - bezel))
    canvas.paste(phone, (x, top), rounded_mask((phone_w, phone_h), radius))
    canvas.save(os.path.join(OUT, 'phone-screenshots', f'{name}_1440x2560.png'), optimize=True)


def feature_graphic():
    src = Image.open(os.path.join(ENTREG, 'encabezado-y-busqueda/encabezado_21x9_3840x1646.png')).convert('RGB')
    target = 1024 / 500
    crop_w = round(src.height * target)
    left = (src.width - crop_w) // 2
    src.crop((left, 0, left + crop_w, src.height)).resize((1024, 500), Image.LANCZOS).save(
        os.path.join(OUT, 'feature-graphic_1024x500.png'), optimize=True)


def icon():
    src = Image.open(os.path.join(ENTREG, 'icon/AppStore-Icon-1024x1024.png')).convert('RGB')
    src.resize((512, 512), Image.LANCZOS).save(os.path.join(OUT, 'icon_512x512.png'), optimize=True)


os.makedirs(os.path.join(OUT, 'phone-screenshots'), exist_ok=True)
for slide in SLIDES:
    screenshot(*slide)
feature_graphic()
icon()
print('ok')

from PIL import Image, ImageDraw, ImageFont
from pygments import highlight
from pygments.lexers import PhpLexer, JsonLexer, XmlLexer
from pygments.token import Token
import os

# Color scheme (Monokai-inspired)
COLORS = {
    Token.Keyword: "#F92672",
    Token.Keyword.Declaration: "#F92672",
    Token.Keyword.Namespace: "#F92672",
    Token.Keyword.Type: "#66D9EF",
    Token.Name: "#F8F8F2",
    Token.Name.Class: "#A6E22E",
    Token.Name.Function: "#A6E22E",
    Token.Name.Function.Magic: "#A6E22E",
    Token.Name.Variable: "#F8F8F2",
    Token.Name.Variable.Instance: "#F8F8F2",
    Token.Name.Attribute: "#A6E22E",
    Token.Name.Tag: "#F92672",
    Token.Name.Builtin: "#66D9EF",
    Token.Name.Other: "#A6E22E",
    Token.String: "#E6DB74",
    Token.String.Double: "#E6DB74",
    Token.String.Single: "#E6DB74",
    Token.String.Interpol: "#F8F8F2",
    Token.Literal.String: "#E6DB74",
    Token.Literal.String.Double: "#E6DB74",
    Token.Number: "#AE81FF",
    Token.Number.Integer: "#AE81FF",
    Token.Number.Float: "#AE81FF",
    Token.Operator: "#F92672",
    Token.Operator.Word: "#F92672",
    Token.Punctuation: "#F8F8F2",
    Token.Comment: "#75715E",
    Token.Comment.Single: "#75715E",
    Token.Comment.Multiline: "#75715E",
    Token.Text: "#F8F8F2",
    Token.Text.Whitespace: "#F8F8F2",
    Token.Generic: "#F8F8F2",
}

BG_COLOR = "#282A36"
LINE_NUM_COLOR = "#6272A4"
DEFAULT_COLOR = "#F8F8F2"
PADDING = 40
LINE_HEIGHT = 22
FONT_SIZE = 14

def get_color(token_type):
    while token_type:
        if token_type in COLORS:
            return COLORS[token_type]
        token_type = token_type.parent
    return DEFAULT_COLOR

def get_font():
    font_names = [
        "C:/Windows/Fonts/consola.ttf",
        "C:/Windows/Fonts/cour.ttf",
        "C:/Windows/Fonts/lucon.ttf",
    ]
    for f in font_names:
        if os.path.exists(f):
            return ImageFont.truetype(f, FONT_SIZE)
    return ImageFont.load_default()

def generate_code_image(code, lexer, output_path, title=""):
    font = get_font()
    tokens = list(lexer.get_tokens(code))

    lines = code.split("\n")
    num_lines = len(lines)
    line_num_width = len(str(num_lines)) * 10 + 20

    # Calculate image dimensions
    max_line_width = 0
    temp_img = Image.new("RGB", (1, 1))
    temp_draw = ImageDraw.Draw(temp_img)
    for line in lines:
        bbox = temp_draw.textbbox((0, 0), line, font=font)
        w = bbox[2] - bbox[0]
        if w > max_line_width:
            max_line_width = w

    title_height = 36 if title else 0
    img_width = PADDING + line_num_width + max_line_width + PADDING + 20
    img_height = title_height + PADDING + (num_lines * LINE_HEIGHT) + PADDING

    img = Image.new("RGB", (img_width, img_height), BG_COLOR)
    draw = ImageDraw.Draw(img)

    # Draw title bar
    if title:
        draw.rectangle([(0, 0), (img_width, title_height)], fill="#21222C")
        title_font = font
        bbox = draw.textbbox((0, 0), title, font=title_font)
        tw = bbox[2] - bbox[0]
        draw.text(((img_width - tw) / 2, 10), title, fill="#6272A4", font=title_font)

    # Draw line numbers
    y = title_height + PADDING
    for i in range(1, num_lines + 1):
        num_str = str(i)
        bbox = draw.textbbox((0, 0), num_str, font=font)
        nw = bbox[2] - bbox[0]
        draw.text((PADDING + line_num_width - nw - 15, y + (i - 1) * LINE_HEIGHT), num_str, fill=LINE_NUM_COLOR, font=font)

    # Draw separator line
    sep_x = PADDING + line_num_width - 5
    draw.line([(sep_x, title_height + PADDING - 5), (sep_x, img_height - PADDING + 5)], fill="#44475A", width=1)

    # Draw syntax-highlighted code
    x = PADDING + line_num_width + 10
    current_x = x
    current_line = 0

    for token_type, value in tokens:
        color = get_color(token_type)
        parts = value.split("\n")
        for j, part in enumerate(parts):
            if j > 0:
                current_line += 1
                current_x = x
            if part:
                draw.text((current_x, title_height + PADDING + current_line * LINE_HEIGHT), part, fill=color, font=font)
                bbox = draw.textbbox((0, 0), part, font=font)
                current_x += bbox[2] - bbox[0]

    img.save(output_path, "PNG")
    print(f"  Generated: {output_path}")

# Read code files
base = os.path.dirname(os.path.abspath(__file__))
jawaban = os.path.join(base, "praktek2_integration_testing_jawaban")
images_dir = os.path.join(base, "images")
os.makedirs(images_dir, exist_ok=True)

files = [
    ("composer.json", "composer_json.png", JsonLexer(), "composer.json"),
    ("phpunit.xml", "phpunit_xml.png", XmlLexer(), "phpunit.xml"),
    (os.path.join("src", "Product.php"), "product.png", PhpLexer(startinline=False), "src/Product.php"),
    (os.path.join("src", "Cart.php"), "cart.png", PhpLexer(startinline=False), "src/Cart.php"),
    (os.path.join("src", "OrderService.php"), "order_service.png", PhpLexer(startinline=False), "src/OrderService.php"),
    (os.path.join("tests", "OrderIntegrationTest.php"), "order_integration_test.png", PhpLexer(startinline=False), "tests/OrderIntegrationTest.php"),
]

print("Generating code images...")
for src_file, img_name, lexer, title in files:
    src_path = os.path.join(jawaban, src_file)
    img_path = os.path.join(images_dir, img_name)
    with open(src_path, "r", encoding="utf-8") as f:
        code = f.read().rstrip()
    generate_code_image(code, lexer, img_path, title)

print("Done! All images saved to images/ folder.")

##
# LR parser generated by the Syntax tool.
#
# https://www.npmjs.com/package/syntax-cli
#
#     npm install -g syntax-cli
#
#     syntax-cli --help
#
# To regenerate run:
#
#     syntax-cli \
#         --grammar ~/path-to-grammar-file \
#         --mode <parsing-mode> \
#         --output ~/parsermodule.py
##

yytext = ''
yyleng = 0
__ = None

EOF = '$'

def on_parse_begin(string):
    pass

def on_parse_end(parsed):
    pass

<<MODULE_INCLUDE>>

<<PRODUCTION_HANDLERS>>

ps = <<PRODUCTIONS>>
tks = <<TOKENS>>
tbl = <<TABLE>>

s = None

<<TOKENIZER>>

def set_tokenizer(custom_tokenizer):
    global _tokenizer
    _tokenizer = custom_tokenizer

def get_tokenizer():
    return _tokenizer

def parse(string):
    global __, yytext, yyleng

    on_parse_begin(string)

    if _tokenizer is None:
        raise Exception('_tokenizer instance wasn\'t specified.')

    _tokenizer.init_string(string)

    s = ['0']

    t = _tokenizer.get_next_token()
    st = None

    while True:
        if t is None:
            _unexpected_end_of_input()

        sta = str(s[-1])
        clm = tks[t['type']]

        if not clm in tbl[sta].keys():
            _unexpected_token(t)

        e = tbl[sta][clm]

        if e[0] == 's':
            s.extend((
                {'symbol': tks[t['type']], 'semantic_value': t['value']},
                e[1:]
            ))
            st = t
            t = _tokenizer.get_next_token()

        elif e[0] == 'r':
            p = ps[int(e[1:])]
            hsa = len(p) > 2
            saa = [] if hsa else None

            if p[1] != 0:
                rhsl = p[1]
                while rhsl > 0:
                    s.pop()
                    se = s.pop()
                    if hsa:
                        saa.insert(0, se['semantic_value'])
                    rhsl = rhsl - 1

            rse = {'symbol': p[0]}

            if hsa:
                yytext = st != None and st['value'] or None
                yyleng = st != None and len(st['value']) or 0

                p[2](*saa);
                rse['semantic_value'] = __

            s.extend((rse, tbl[s[-1]][str(p[0])]))

        elif e == 'acc':
            s.pop()
            parsed = s.pop()

            if len(s) != 1 or s[0] != '0' or _tokenizer.has_more_tokens():
                _unexpected_token(t)

            if parsed.has_key('semantic_value'):
                on_parse_end(parsed['semantic_value'])
                return parsed['semantic_value']

            on_parse_end(True)
            return True

        if not _tokenizer.has_more_tokens() and len(s) <= 1:
            break

def _unexpected_token(token):
    if token['value'] == EOF:
        _unexpected_end_of_input()
    _parse_error(
        'Unexpected token: "' + str(token['value']) + '" at ' +
        str(token['start_line']) + ':'  + str(token['start_column']) + '.'
    )


def _unexpected_end_of_input():
    _parse_error('Unexpected end of input.')

def _parse_error(message):
    raise Exception('Parse error: ' + str(message))



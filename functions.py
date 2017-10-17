import numpy
import time
import pylab
import yaml

def load_config():
    with open("config.yaml", 'r') as stream:
        try:
            return yaml.load(stream)
        except yaml.YAMLError as exc:
            print(exc)

config = load_config()

def parse_csv(csv, delimiter): 
    titles = []
    data = []
    for line in csv.split("\n"):
        cols = line.split(delimiter)
        data_line = {}
        if len(titles) == 0:
            # Title
            for title in cols:
                titles.append(title)
        else:
            # Common line

            # End of file?
            if len(cols) == 1 and cols[0] == '':
                continue

            col_num = 0
            for value in cols:
                data_line[titles[col_num]] = value
                col_num = col_num + 1

            data.append(data_line)

    return data

def get_trend(y):
    return numpy.polyfit(range(len(y)), y, 1)

def show_plot(y, trendline=False, save=False, movingAverage=False, movingAverageExp=False):
    x = range(len(y))
    lines = pylab.plot(x, y, "b")
    pylab.setp(lines, linewidth=2)

    # Trendline
    if trendline:
        p = numpy.poly1d(get_trend(y))
        pylab.plot(x, p(x), "r-")

    # Moving average
    if movingAverage:
        pylab.plot(x, moving_average(y), "c-")

    # Moving average exponential
    if movingAverageExp:
        pylab.plot(x, moving_average_exp(y), "m-")

    pylab.grid(1)
    if not save:
        pylab.show()
    else:
        pylab.savefig(save)
    pylab.clf()

def next_values_by_trendline(y, number_of_values):
    a, b = get_trend(y)
    out = []
    for i in range(number_of_values):
        out.append((len(y) + 1 + i) * a + b)
    return out

#def get_rss_urls(url, date=False, wantGrade=False):
#    f = feedparser.parse(url)
#    out = []
#    for entry in f.entries:
#        # Just for given date?
#        date_parsed = "{0}-{1}-{2}".format(
#            entry.published_parsed.tm_year,
#            entry.published_parsed.tm_mon,
#            entry.published_parsed.tm_mday)
#        if date and date != date_parsed:
#            continue
#        # Grade it?
#        if wantGrade:
#            grade = article.grade_article(article.get_article(entry.links[0]['href']))
#        else:
#            grade = 0
#        # Remove redirect
#        url = "http://" + entry.links[0]['href'].split("http://")[-1]
#        # Append dictionary data
#        out.append({'date':date_parsed, 'url':url, 'grade':grade})
#    return out

def date_now():
    return time.strftime("%Y-%m-%d").replace('-0', '-')

def percent_between(a, b):
    if a == 0: return 0
    return 100 * (float(b)-float(a))/float(a)

def moving_average(prices, wide=4):
    ret = numpy.cumsum(prices, dtype=float)
    ret[wide:] = ret[wide:] - ret[:-wide]
    out = list(ret[wide - 1:] / wide)
    for i in range(wide-1):
        out.insert(0, out[0])
    return out

def moving_average_exp(prices, wide=8):
    exp = 2 / (wide + 1)
    out = []
    for i in range(len(prices)):
        if i < wide:
            out.append(prices[i])
            continue
        a = (out[-1] * exp) + prices[i-1] * (1 - exp)
        out.append(a)
    return out

def find_extremes(prices):
    extreme = []
    extreme.append(prices[0])
    extreme.append(prices[1])
    if extreme[0] < extreme[1]:
        last = 'high'
    else:
        last = 'low'

    for i in range(2, len(prices)):
        # Higher
        if prices[i] > extreme[-1] and last == 'high':
            # New high
            extreme[-1] = prices[i]
            last = 'high'
        if prices[i] > extreme[-1] and last == 'low':
            # Change of direction - goin' high
            extreme.append(prices[i])
            last = 'high'

        # Lower
        if prices[i] < extreme[-1] and last == 'low':
            # New low
            extreme[-1] = prices[i]
            last = 'low'
        if prices[i] < extreme[-1] and last == 'high':
            # Change of direction - goin' low
            extreme.append(prices[i])
            last = 'low'

    return extreme

def tony_plummer(prices, current_price):
    extremes = find_extremes(prices)
    P1 = extremes[-3]
    P2 = extremes[-2]

    # Bull market
    if P1 > P2:
        Pt = P2 + (P1 - P2) * 2.618
    # Bear market
    else:
        Pt = P2 - (P2 - P1) * 2.618

    return Pt

def elliot_waves(prices):
    extremes = find_extremes(prices)

    # Find highs only
    highs = []
    for i in range(1, len(extremes)):
        if extremes[i] > extremes[i-1]:
            highs.append(extremes[i])

    # Last 2 highs are getting lower
    if highs[-1] < highs[-2]:
        return 1
    # Last 3 highs are getting higher
    if highs[-1] > highs[-2] and highs[-2] > highs[-3]:
        return -1
    return 0

import requests
from westwallet_api import WestWalletAPI
from westwallet_api.exceptions import BadAddressException, InsufficientFundsException
import os
import re
import socket
import random
from googleapiclient.discovery import build
from isodate import parse_duration
import datetime as dt


WESTWALLET_PUBLIC_KEY = os.environ['WESTWALLET_PUBLIC_KEY']
WESTWALLET_PRIVATE_KEY = os.environ['WESTWALLET_PRIVATE_KEY']
GOOGLE_API_KEY = os.environ['GOOGLE_API_KEY']

ALPHABETS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z']

all_coins = {'btc': 'Bitcoin', 'eth': 'Ethereum', 'usdttrc': 'USDT TRC-20', 'ltc': 'Litecoin', 'bch': 'Bitcoin Cash', 'etc': 'Ethereum Classic', 'zec': 'Zcash', 'bnb': 'Binance Coin BEP-20', 'xrp': 'Ripple', 'eos': 'EOS', 'ada': 'Cardano', 'trx': 'TRON', 'doge': 'Dogecoin', 'sol': 'Solana', 'xmr': 'Monero', 'shib': 'Shiba Inu', 'usdctrc': 'USD Coin', 'busd': 'Binance USD', 'dash': 'Dash'}
fees = {
    'btc': {
        'send': 0.0002,
        'receive': 0.0002
    },'eth': {
        'send': 0.0015,
        'receive': 0.0015
    },'usdttrc': {
        'send': 2,
        'receive': 1
    },'ltc': {
        'send': 0.03,
        'receive': 0.01
    },'bch': {
        'send': 0.03,
        'receive': 0.01
    },'etc': {
        'send': 0.07,
        'receive': 0.03
    },'zec': {
        'send': 0.03,
        'receive': 0.01
    },'bnb': {
        'send': 0.007,
        'receive': 0.002
    },'xrp': {
        'send': 1.5,
        'receive': 0.5
    },'eos': {
        'send': 0.4,
        'receive': 0.1
    },'ada': {
        'send': 1.7,
        'receive': 1
    },'trx': {
        'send': 8,
        'receive': 2
    },'doge': {
        'send': 13,
        'receive': 4
    },'sol': {
        'send': 0.07,
        'receive': 0.02
    },'xmr': {
        'send': 7,
        'receive': 2
    },'shib': {
        'send': 400000,
        'receive': 50000
    },'usdctrc': {
        'send': 1.7,
        'receive': 0.2
    },'busd': {
        'send': 1.7,
        'receive': 0.2
    },'dash': {
        'send': 0.01,
        'receive': 0.01
    },
}


def get_ticker_from_binance(symbol: str, conversion=False, **kwargs):
    """

    :param symbol: Crypto symbol
    :param conversion: Set to True if you want to make conversion
    :param kwargs: If conversion is set to True, include: 'direction' (fd is to USD, bk is otherwise), 'amount' (amount to be converted)
    :return: Returns price of symbol in USD or result of conversion if set
    """
    if symbol != "usdttrc":
        response = requests.get(f"https://api.binance.com/api/v3/ticker/24hr?symbol={symbol.upper()}USDT").json()
        price = float(response['askPrice'])
    else:
        price = 1
    if conversion:
        if kwargs['direction'] == "fd":
            return round(price * float(kwargs['amount']), 2)
        return float(kwargs['amount']) / price
    return price


def generate_random_wallet(token):
    wallet = token
    for _ in range(30):
        wallet += random.choice(ALPHABETS)
    return wallet


def generate_wallet(coin, label=""):
    client = WestWalletAPI(WESTWALLET_PUBLIC_KEY, WESTWALLET_PRIVATE_KEY)
    try:
        address = client.generate_address(currency=coin.upper(), ipn_url="", label=label)
    except:
        address = generate_random_wallet(coin)
        return address
    else:
        return address.address


def make_withdrawal(token, qty, addr):
    client = WestWalletAPI(WESTWALLET_PUBLIC_KEY, WESTWALLET_PRIVATE_KEY)
    try:
        response = client.create_withdrawal(currency=token, amount=qty, address=addr)
    except InsufficientFundsException:
        return {'status': 'error', 'message': 'An error occurred (PRR099), please try again later'}
    except BadAddressException:
        return {'status': 'error', 'message': 'Bad address provided, please try again later'}
    except:
        return {'status': 'error', 'message': 'An error occurred (PRR104), please try again later'}
    else:
        return response.__dict__


def is_address_valid(token, addr):
    response = requests.get(url="https://api.westwallet.io/wallet/currencies_data")
    for info in response.json():
        if token.upper() in info['tickers']:
            if re.match(info['address_regex'], addr):
                return True
            return False
    return False


def get_token_info(token=None, info=None):
    """

    :param token: currency to retrieve
    :param info: the parameter to get: min_receive, min_withdraw
    :return: returns value
    """
    response = requests.get(url="https://api.westwallet.io/wallet/currencies_data")
    if token and info:
        for res in response.json():
            if token.upper() in res['tickers']:
                return res[info]
        return None
    return response


def get_all_tickers():
    coins_list = [f'"{coin.upper()}USDT"' for coin in list(all_coins) if coin != 'usdttrc']
    coins_list = f'%5B{",".join(coins_list)}%5D'
    response = requests.get(f"https://binance.me/api/v3/ticker/price?symbols={coins_list}").json()
    our_dict = {}
    for res in response:
        our_dict[res['symbol'][:-4].lower()] = res['price']
    our_dict['usdttrc'] = 1
    return our_dict


youtube = build('youtube', 'v3', developerKey=GOOGLE_API_KEY)


def st(timestamp):
    # timestamp = abs(int(dt.datetime.now().timestamp()) - int(timestamp))
    if timestamp == 0:
        return "moments"
    days = int(timestamp / (60 * 60 * 24))
    hrs = int(timestamp % (60 * 60 * 24) / (60 * 60))
    mins = int(timestamp % (60 * 60) / 60)
    secs = (timestamp % (60 * 60)) % 60
    time_str = ''
    if days > 0:
        days_str = 'day'
        if days > 1:
            days_str = "days"
        return f'{days} {days_str}'
    elif hrs > 0:
        hrs_str = 'hr'
        if hrs > 1:
            hrs_str = 'hrs'
        return f'{hrs} {hrs_str}'
    elif mins > 0:
        mins_str = 'min'
        if mins > 1:
            mins_str = 'mins'
        return f'{mins} {mins_str}'
    elif secs > 0:
        secs_str = 'sec'
        if secs > 1:
            secs_str = 'secs'
        return f'moments'


def search_videos_by_category(category_id, page_token='', ads="any"):
    if page_token == "":
        response = youtube.videos().list(
            part='snippet,contentDetails',
            chart='mostPopular',
            regionCode='US',
            videoCategoryId=category_id,
            maxResults=25
        ).execute()
    else:
        response = youtube.videos().list(
            part='snippet,contentDetails',
            chart='mostPopular',
            regionCode='US',
            videoCategoryId=category_id,
            maxResults=25,
            pageToken=page_token
        ).execute()

    response = response['items']
    response_list = {}

    for res in response:
        response_list[res['id']] = {
            'title': res['snippet']['title'],
            'description': res['snippet']['description'],
            'image': res['snippet']['thumbnails']['high']['url'],
            'created_at': res['snippet']['publishedAt'],
            'length': st(parse_duration(res['contentDetails']['duration']).total_seconds()),
            'projection': res['contentDetails']['projection']
        }
    return response_list


def get_video_stats(video_list: list):
    list_to_use = video_list[:-1]
    random.shuffle(list_to_use)
    ids = ",".join(list_to_use)
    request = youtube.videos().list(
        part="snippet,contentDetails",
        id=ids
    )
    response = request.execute()['items']
    response_list = {}

    for res in response:
        response_list[res['id']] = {
            'title': res['snippet']['title'],
            'description': res['snippet']['description'],
            'image': res['snippet']['thumbnails']['high']['url'],
            'created_at': res['snippet']['publishedAt'],
            'length': st(parse_duration(res['contentDetails']['duration']).total_seconds()),
            'length_in_sec': parse_duration(res['contentDetails']['duration']).total_seconds(),
            'projection': res['contentDetails']['projection']
        }
    response_list['page_token'] = video_list[-1]
    return response_list


def search_videos(search_term, page_token='', ads="any"):
    """
    :param search_term: string
    :param ads: 'true' or (default: 'any')
    :param page_token: string
    :return: videos
    """
    if page_token == "":
        response = youtube.search().list(
            part="snippet",
            maxResults=25,
            q=search_term,
            type="video",
            videoDuration="short",
            videoPaidProductPlacement=ads,
            videoSyndicated="true",
            safeSearch="strict"
        ).execute()
    else:
        response = youtube.search().list(
            part="snippet",
            maxResults=25,
            q=search_term,
            type="video",
            videoDuration="short",
            videoPaidProductPlacement=ads,
            videoSyndicated="true",
            safeSearch="strict",
            pageToken=page_token
        ).execute()

    id_list = []
    for res in response['items']:
        id_list.append(res['id']['videoId'])
    try:
        id_list.append(response['nextPageToken'])
    except KeyError:
        id_list.append('')

    return get_video_stats(id_list)


# print(search_videos('music'))

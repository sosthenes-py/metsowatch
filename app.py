"""
TODO: When in production, in the create video route, set status for new videos to 0 for pending
TODO: in production, remove link for admin uploads from the creator page
for ref_earning in program_history, column 'label' represents the ref level which added that ref bonus row and column 'detail' represents the member id of the downline

name of default thumbnail for videos is website-default.jpg

"""
import os
import random
import re
import uuid
from decimal import Decimal
from functools import wraps
import requests

import pyotp
import qrcode
import youtube_dl
from flask import Flask, render_template, session, redirect, url_for, request, jsonify, flash, abort, send_from_directory, make_response
from flask_wtf.csrf import CSRFProtect
import datetime as dt
from flask_migrate import Migrate
from flask_login import LoginManager, login_user, logout_user, login_required, current_user
from sqlalchemy import func

from db_models import db, Member, Session, ProgramHistory, Downline, Address, Notification, PayoutAccount, Video, VideoWatched, AdminMember, Task, Visit
from flask_limiter import Limiter
from werkzeug.security import check_password_hash, generate_password_hash
from forms import LoginForm, RegisterForm, AddWithdrawAccountForm, WithdrawForm, ChangePasswordForm, TwoFaForm, CreateVideoForm, AdminLoginForm, AdminRegisterForm
import api
from moviepy.editor import VideoFileClip
from pytube import YouTube
from pytube.exceptions import RegexMatchError, VideoUnavailable, PytubeError


SITE_NAME = "METSOWATCH"
SITE_DOMAIN = "192.168.210.231:5000"
app = Flask(__name__)
app.config['SECRET_KEY'] = "gfdcvbkjiuhygtfdcgvhjk541564bvgcvbjhg"
app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://metsowatch_user:Lk3mWsOHUDE3Vi82AaC7VhzsAZepUy7l@dpg-cm8breq1hbls73b05dr0-a.frankfurt-postgres.render.com/metsowatch'
# app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///mydb.db'
# app.config['SQLALCHEMY_DATABASE_URI'] = os.environ['SQLALCHEMY_DATABASE_URI']
app.config['ALLOWED_VIDEO_EXTENSIONS'] = {'mp4', 'mkv', 'avi'}
app.config['ALLOWED_IMAGE_EXTENSIONS'] = {'jpg', 'png', 'jpeg'}
app.config['IMAGE_UPLOAD_FOLDER'] = 'static/uploads/images'
app.config['VIDEO_UPLOAD_FOLDER'] = 'static/uploads/videos'

db.init_app(app)
login_manager = LoginManager(app)
Migrate(app=app, db=db)
csrf = CSRFProtect(app)

limiter = Limiter(key_func=lambda: request.remote_addr, app=app, storage_uri="memory://", headers_enabled=True)

login_manager.login_view = "login"
login_manager.login_message = "You are out of session. Please login again"


with app.app_context():
    db.create_all()


@app.context_processor
def inject_globals():
    return dict(my_round=my_round, format_date=format_date, session=session, plans=PLANS, ref_bonus=REF_BONUS, profit=PROFIT, site_domain=SITE_DOMAIN, promotion_bonus=PROMOTION_BONUS, deposit_wallets=DEPOSIT_WALLETS, all_coins=api.all_coins, notif_cat=NOTIFICATION_CATEGORIES, withdrawal_fee=WITHDRAWAL_FEE, tx_per_page=TX_PER_PAGE, site_name=SITE_NAME, video_creator_bonus=VIDEO_CREATOR_BONUS, new_user_video_reward=NEW_USER_VIDEO_REWARD)


REF_BONUS = [8, 5, 2, 0.5, 0.3, 0.2]

PROFIT = 3.5

NEW_USER_VIDEO_REWARD = 0.2

VIDEO_CREATOR_BONUS = 5

PROMOTION_BONUS = 0.5

DEPOSIT_WALLETS = ['btc', 'usdttrc', 'trx']

MIN = {
    'btc': 0.0005,
    'usdttrc': 10,
    'trx': 20
}

NOTIFICATION_CATEGORIES = {
    'upgrade': 'medal',
    'deposit confirmation': 'check'
}

PLANS = [
{
    'id': 0,
    'price': 0,
    'profit': 0.1,
    'videos': 10
}, {
    'id': 1,
    'price': 10,
    'profit': 0.35,
    'videos': 10
}, {
    'id': 2,
    'price': 50,
    'profit': 1.75,
    'videos': 10
}, {
    'id': 3,
    'price': 120,
    'profit': 3.5,
    'videos': 10
}, {
    'id': 4,
    'price': 300,
    'profit': 10.5,
    'videos': 10
}, {
    'id': 5,
    'price': 550,
    'profit': 17.5,
    'videos': 10
}, {
    'id': 6,
    'price': 1000,
    'profit': 10.5,
    'videos': 9
}, {
    'id': 7,
    'price': 3200,
    'profit': 10.5,
    'videos': 8
}, {
    'id': 8,
    'price': 5000,
    'profit': 10.5,
    'videos': 7
}, {
    'id': 9,
    'price': 12000,
    'profit': 10.5,
    'videos': 6
}, {
    'id': 10,
    'price': 25000,
    'profit': 10.5,
    'videos': 5
}]

WITHDRAWAL_FEE = 2


TX_PER_PAGE = 10


def my_round(value):
    if '.' in str(value):
        after_dec = str(value).split('.')[1]
        count = len(after_dec)
        if count >= 4:
            return int(value * (10 ** 4)) / (10 ** 4)
        return value
    return value


def format_date(timestamp, fmt='%d %b %H:%M', option_limit: int = None):
    if timestamp == 0:
        return 'NULL'
    if option_limit:
        if dt.datetime.now().timestamp() - int(timestamp) < option_limit:
            return f'{st(timestamp)} ago'
    return f"{dt.datetime.fromtimestamp(int(timestamp)):{fmt}}"


def st(timestamp, video=False):
    if not video:
        timestamp = abs(int(dt.datetime.now().timestamp()) - int(timestamp))
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
        if video:
            secs_str = 'sec'
            if secs > 1:
                secs_str = 'secs'
            return f'{secs} {secs_str}'
        return f'moments'


def get_timestamp():
    return int(dt.datetime.now().timestamp())


def session_validate(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        if session.get('session_id', 'None') not in [sess.token for sess in current_user.sessions] or current_user.suspend:
            flash('Session has ended. Login again', 'error')
            return redirect(url_for('login'))
        return func(*args, **kwargs)
    return wrapper


def is_strong_password(password):
    if len(password) < 8:
        return [False, 'Password length must be greater than 7']
    if not any(char.isupper() for char in password):
        return [False, 'Password must include an upper case letter']
    if not any(char.islower() for char in password):
        return [False, 'Password must include a lower case letter']
    if not any(char.isdigit() for char in password):
        return [False, 'Password must include a digit']
    special_characters = re.compile(r'[!@#$%^&*(),.?":{}|<>]')
    if not special_characters.search(password):
        return [False, 'Password must include a special character']
    return [True, 'success']


def generate_tx_id(length=6):
    code = ''
    for _ in range(length):
        code += f'{random.randint(0, 9)}'
    if code[0] == '0':
        code = f'{code[1:]}0'
    return code


def initiate_withdrawal(amt, wallet, currency='usdttrc'):
    pass


def is_english_char(char):
    code_point = ord(char)
    return 65 <= code_point <= 90 or 97 <= code_point <= 122


def get_initials(string, length=2):
    initials = ''
    for x in string:
        if len(initials) <= length-1:
            if is_english_char(x):
                initials += x
        else:
            return initials


def hash_string(string):
    return f'{string[:int(len(string)/2)]}***'


def register_upline(user: Member, upline_id):
    for x in range(1, 7):
        if upline_id:
            result = db.session.query(Member).filter_by(id=int(upline_id)).first()
            if x == 1:
                if len([dn for dn in result.downlines if dn.level == 1]) < 10:
                    result.promotion += PROMOTION_BONUS
                    new_history = ProgramHistory(name='ref_earning', time=get_timestamp(), amt=PROMOTION_BONUS, tx_id=generate_tx_id(), member_id=result.id, detail=user.id, label=1)
                    db.session.add(new_history)
            new_down = Downline(level=x, upline_id=result.id, downline_id=user.id, time=get_timestamp(), downline_name=user.email.split('@')[0])
            db.session.add(new_down)
            upline_id = result.upline
    db.session.commit()


def reward_upline(user: Member, amt):
    upline_id = user.upline
    for x in range(1, 7):
        if upline_id:
            ref_bonus = float(f'{(REF_BONUS[x-1]/100)*amt:.2f}')
            result = db.session.query(Member).filter_by(id=int(upline_id)).first()
            result.ref_earning += ref_bonus
            result.history.append(ProgramHistory(member_id=result.id, name='ref_earning', amt=ref_bonus, status=1, time=get_timestamp(), tx_id=generate_tx_id(7), label=x, detail=result.id))
            upline_id = result.upline


def get_level(upline: Member, downline: Member):
    return int([this_user.level for this_user in upline.downlines if this_user.downline_id == downline.id][0])


def get_downline_bonus(upline: Member, downline: Member):
    total_bonus = 0
    result = ProgramHistory.query.filter(ProgramHistory.member_id == upline.id, ProgramHistory.name == "ref_earning", ProgramHistory.detail == str(downline.id)).all()
    for history in result:
        total_bonus += history.amt
    return total_bonus


def get_downlines(user: Member, fetch_level: int, q: str = None):
    if current_user == user:
        level = 1
    else:
        level = get_level(current_user, user)
        if level > 6:
            raise "ERROR"

    tree = f"""
        <div class="hv-item-parent">
            <div class="person">
                <div class="f-name-l-name">{get_initials(user.email)}</div>
                <p class="name">
                    {'Me' if user == current_user else 'My Level {} Link ({})'.format(level, hash_string(user.email.split('@')[0]))}
                </p>
            </div>
        </div>
        <div class="hv-item-children">
    """

    if q:
        result = Downline.query.filter(Downline.downline_name.like(f'{q}%'), Downline.upline_id == current_user.id).order_by(Downline.id.desc()).all()
    else:
        result = Downline.query.filter(Downline.upline_id == user.id).order_by(Downline.id.desc()).all()
    total_bonus = 0
    total_links = 0
    if result:
        for down in result:
            downline = Member.query.filter_by(id=down.downline_id).first()
            if fetch_level == get_level(upline=user, downline=downline) <= 6:
                total_bonus += get_downline_bonus(upline=user, downline=downline)
                total_links += 1
                tree += f"""
                    <div class="hv-item-child child" data-id="{downline.code}">
                        <!-- Key component -->
                        <div class="hv-item">
                            <div class="hv-item-parent">
                            <div class="person">
                                <div class="f-name-l-name">{get_initials(downline.email)}</div>
                                <p class="name">
                                    <b>{hash_string(downline.email.split('@')[0])}<br>
                                    <span class="yellow-color">Level {downline.level}</span><br>
                                    {len([dn for dn in downline.downlines if dn.level == 1])} Direct Link
                                    </b>
                                </p>
                            </div>
                            </div>
                        </div>
                    </div>
                """

    tree += """
    </div>
    """
    return {'level': level, 'tree': tree, 'bonus': total_bonus, 'links': total_links}


def valid_otp(code):
    totp = pyotp.TOTP(current_user.twofa_secret)
    if not totp.verify(code):
        return False
    return True


def allowed_file(filename, type_):
    if type_ == "video":
        return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_VIDEO_EXTENSIONS']
    else:
        return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_IMAGE_EXTENSIONS']


@app.route('/static/<path:filename>')
def static_file(filename):
    return send_from_directory('static', filename, cache_timeout=86400*30)


@login_manager.user_loader
def load_user(user):
    if session['role'] == 'user':
        return db.session.query(Member).filter_by(id=user).first()
    return db.session.query(AdminMember).filter_by(id=user).first()


@app.route('/')
def start_page():
    if not session.get('upline'):
        session['upline'] = ''
    return render_template('home/start.html')


@app.route('/promotion/<code>')
def ref_receive(code):
    try:
        coded = int(code)
    except TypeError:
        abort(404)
    except ValueError:
        abort(404)
    if db.session.query(Member).filter_by(code=code).first():
        session['upline'] = code
    else:
        session.pop('upline')
    return redirect(url_for('start_page'))


@app.route('/home')
def home_page():
    return render_template('home/index.html')


@limiter.limit('20 per minute', error_message='You are rate limited. Try again after a minute')
@app.route('/register', methods=['POST', 'GET'])
def register():
    if request.method == "GET":
        form = RegisterForm()
        return render_template('home/register.html', form=form)

    form = RegisterForm(request.form)
    if form.validate():
        email = form.email.data
        country = request.form['country']
        phone = request.form['phone']
        password = form.password.data
        upline = session.get('upline')
        if upline and upline != "":
            upline = db.session.query(Member).filter_by(code=upline).first()
        else:
            upline = None

        if email == "" or country == "" or phone == "" or password == "":
            return jsonify({'status': 'error', 'message': 'All fields are required'})
        if db.session.query(Member).filter_by(email=email).first():
            return jsonify({'status': 'error', 'message': 'That email address already exists'})
        if not is_strong_password(password)[0]:
            return jsonify({'status': 'error', 'message': is_strong_password(password)[1]})

        new_user_bonus = request.cookies.get('bonus')
        if new_user_bonus:
            promotion = NEW_USER_VIDEO_REWARD
        else:
            promotion = 0

        code = generate_tx_id(6)
        new_user = Member(email=email, password=generate_password_hash(password), code=code,
                          email2=email, level=0, country=country, phone=phone, upline=upline.id if upline else None, today_watch=PLANS[0]['videos'], reg_date=get_timestamp(), promotion=promotion)
        db.session.add(new_user)
        db.session.commit()

        for token in DEPOSIT_WALLETS:
            new_addr = Address(member_id=new_user.id, label='complete', wallet=api.generate_wallet(token, f'{new_user.id}=complete'), token=token, time=get_timestamp())
            db.session.bulk_save_objects([new_addr])
        db.session.commit()

        if upline:
            register_upline(new_user, upline.id)
        return jsonify({'status': 'success', 'message': 'Your account has been created'})
    return jsonify({'status': 'error', 'message': form.email.errors or form.password.errors})


@app.route('/login', methods=['POST', 'GET'])
def login():
    if request.method == "GET":
        form = LoginForm()
        next_ = request.args.get('next')
        return render_template('home/login.html', form=form, next=next_)

    form = LoginForm(request.form)
    if form.validate():
        email = form.email.data
        password = form.password.data
        if email == "" or password == "":
            return jsonify({'status': 'error', 'message': 'All fields are required'})
        result = db.session.query(Member).filter_by(email=email).first()
        if result:
            if check_password_hash(result.password, password):
                if not result.suspend:
                    session['role'] = 'user'
                    login_user(result)
                    session_id = str(uuid.uuid4())
                    session['session_id'] = session_id
                    time = get_timestamp()
                    if result.logins is None:
                        result.logins = f'{time}={time}'
                    else:
                        time1, time2 = result.logins.split("=")
                        result.logins = f'{time1}={time}'

                    # SESSION
                    user_agent, ip = request.user_agent.string, request.remote_addr
                    if result.sessions:
                        result.sessions = []
                    new_session = Session(member_id=result.id, token=session_id, time=time, ip=ip, device=user_agent)
                    db.session.add(new_session)
                    db.session.commit()
                    next_ = request.args.get('next', url_for('user_videos'))
                    print(f"--------------{request.args.get('next')}")
                    return jsonify({'status': 'success', 'message': 'Login success', 'next': next_})
                return jsonify({'status': 'error', 'message': 'Account has been temporarily suspended'})
            return jsonify({'status': 'error', 'message': 'Invalid login details'})
        return jsonify({'status': 'error', 'message': 'Invalid login details'})
    return jsonify({'status': 'error', 'message': form.email.errors or form.password.errors})


@app.route('/user/logout', methods=['GET'])
def logout():
    logout_user()
    return redirect(url_for('login'))


@app.route('/user/wallet', methods=['POST', 'GET'])
@login_required
@session_validate
def user_wallet():
    today = dt.datetime.now()
    start_of_the_week = today - dt.timedelta(days=today.weekday())
    this_week_earning = 0
    for history in current_user.history:
        if history.name == "earning":
            if today >= dt.datetime.fromtimestamp(int(history.time)) >= start_of_the_week:
                this_week_earning += history.amt
    return render_template('account/wallet.html', page='wallet', this_week_earning=this_week_earning)


@app.route('/user/packages', methods=['POST', 'GET'])
@limiter.limit('10 per minute', error_message='You are rate limited. Try again after a minute')
@login_required
@session_validate
def user_packages():
    if request.method == "GET":
        return render_template('account/packages.html', page='packages')

    elif request.method == "POST":
        level = request.form['level']
        action = request.form['action']

        try:
            level = int(level)
        except ValueError:
            raise "Error1"
        if level not in range(1, 11):
            raise "Error2"

        if current_user.level >= level:
            raise "Error3"

        # CHECK FOR PREVIOUS PENDING UPGRADE
        result = db.session.query(Address).filter(Address.member_id == current_user.id, Address.label == "complete", Address.upgrade_level > 0).first()
        if result:
            level = result.upgrade_level
            message = f'You have a pending upgrade to level {level}'
            amt_to_pay = result.amt_to_pay
            current_level_price = PLANS[current_user.level]['price']
            next_level_price = PLANS[level]['price']
            bal = result.hold_amt
            leftover = result.leftover

            if action == "verify":
                info = f"""
                        Click <span class="special-text">Proceed</span> to continue with a payment of <b>${amt_to_pay:,.2f}</b> for the purchase of this subscription.
                        """

                math = f"""
                            <math xmlns="http://www.w3.org/1998/Math/MathML">
                               <mrow>
                                  <mrow>
                                    <mn>{next_level_price:,}</mn>
                                    <mo>-</mo>
                                    <mo>(</mo>
                                    <mrow>
                                      <mn>{current_level_price:,}</mn>
                                      <mo>+</mo>
                                      <mn>{bal:,.2f}</mn>
                                    </mrow>
                                    <mo>)</mo>
                                  </mrow>
                                  <mo>=</mo>
                                  <mn ><span class='double-underline fw-bold'>${amt_to_pay:,.2f}</span> &nbsp;&nbsp;<i class="anticon anticon-check" style="font-size: 1.9em; color: #ffc300"></i></mn>
                                </mrow>
        
                              </math>
                            """

                math_left = f"""
                                    <math xmlns="http://www.w3.org/1998/Math/MathML">
                                        <mrow>
                                          <mo>=</mo>
                                          <mn>${leftover:,.2f}</mn>
                                        </mrow>
                                      </math>
                                    """

                return jsonify({'status': 'success', 'net': f'{amt_to_pay:,.2f}', 'left_over': f'{leftover:,.2f}',
                                'level_fee': f'{next_level_price:,}', 'math': math, 'math_left': math_left, 'info': info, 'message': message, 'bal': f'{bal:,.2f}', 'level': level})

            elif action == "finish":
                return jsonify({'status': 'success', 'message': 'Previous upgrade fetched', 'type': 'pay', 'amt_to_pay': f'{amt_to_pay:,.2f}', 'amt': amt_to_pay})

            elif action == "fetch_wallet":
                # FETCH WALLET DETAILS
                token = request.form['token']
                qty = api.get_ticker_from_binance(token, conversion=True, direction='bk', amount=amt_to_pay)
                if qty < MIN[token]:
                    message = 'Amount is too small for this method, please select another method'
                    status = 'error'
                else:
                    message = ''
                    status = 'success'
                    Address.query.filter(Address.member_id == current_user.id, Address.token == token, Address.label == "complete").update({'rate_time': get_timestamp(), 'qty_to_pay': qty})
                    db.session.commit()
                return jsonify({'status': status, 'message': message, 'qty': f'{my_round(qty):,}'})


        else:
            # IF NO HISTORY OF PENDING UPGRADE

            current_level_price = PLANS[current_user.level]['price']
            next_level_price = PLANS[level]['price']
            bal = current_user.earning + current_user.ref_earning + current_user.promotion

            amt_to_pay = next_level_price - current_level_price
            amt_to_pay -= bal
            leftover = 0
            if amt_to_pay < 0:
                leftover = abs(amt_to_pay)
                amt_to_pay = 0

            if action == "verify":
                info = f"""
                            Click <span class="special-text">Proceed</span> to continue with a payment of <b>${amt_to_pay:,.2f}</b> for the purchase of this subscription.
                            """

                if amt_to_pay == 0:
                    info = f"""
                                No extra deposits! Just click <span class="special-text">Proceed</span> to upgrade now. <br>A left over sum of ${leftover:,.2f} will be returned into your earnings wallet.
                                """

                math = f"""
                <math xmlns="http://www.w3.org/1998/Math/MathML">
                   <mrow>
                      <mrow>
                        <mn>{next_level_price:,}</mn>
                        <mo>-</mo>
                        <mo>(</mo>
                        <mrow>
                          <mn>{current_level_price:,}</mn>
                          <mo>+</mo>
                          <mn>{bal:,.2f}</mn>
                        </mrow>
                        <mo>)</mo>
                      </mrow>
                      <mo>=</mo>
                      <mn ><span class='double-underline fw-bold'>${amt_to_pay:,.2f}</span> &nbsp;&nbsp;<i class="anticon anticon-check" style="font-size: 1.9em; color: #ffc300"></i></mn>
                    </mrow>
            
                  </math>
                """

                math_left = f"""
                        <math xmlns="http://www.w3.org/1998/Math/MathML">
                            <mrow>
                              <mo>=</mo>
                              <mn>${leftover:,.2f}</mn>
                            </mrow>
                          </math>
                        """

                return jsonify({'status': 'success', 'net': f'{amt_to_pay:,.2f}', 'left_over': f'{leftover:,.2f}', 'level_fee': f'{next_level_price:,}', 'math': math, 'math_left': math_left, 'info': info, 'message': '', 'bal': f'{bal:,.2f}'})

            elif action == "finish":
                if amt_to_pay > 0:
                    db.session.query(Address).filter(Address.member_id == current_user.id, Address.label == "complete").update({'amt_to_pay': float(f'{amt_to_pay:.2f}'), 'upgrade_level': level, 'hold_amt': bal, 'leftover': leftover})
                    current_user.earning, current_user.ref_earning, current_user.promotion = 0, 0, 0
                    new_notif = Notification(member_id=current_user.id, category='upgrade', time=get_timestamp(), body=f'Upgrade to level {level} pending. Follow instructions on upgrade page to complete payment')
                    db.session.add(new_notif)
                    db.session.commit()
                    return jsonify({'status': 'success', 'message': 'Balance deducted accordingly', 'type': 'pay', 'amt_to_pay': f'{amt_to_pay:,.2f}', 'amt': amt_to_pay})
                else:
                    new_notif = Notification(member_id=current_user.id, category='upgrade', time=get_timestamp(), body=f'Successfully upgraded from level {current_user.level} to level {level}')
                    db.session.add(new_notif)
                    current_user.level, current_user.earning, current_user.ref_earning, current_user.promotion = level, leftover, 0, 0
                    reward_upline(current_user, next_level_price)
                    db.session.commit()

                    if level > 3:
                        rank_img = 'rank0.svg'
                    else:
                        rank_img = f'rank{level}.svg'
                    return jsonify({'status': 'success', 'message': 'Upgrade successful', 'type': 'done', 'rank_img': rank_img})

    elif request.method == 'PUT':
        pass


@app.route('/user/notifications', methods=['POST', 'GET'])
@login_required
@session_validate
def user_notifications():
    if request.method == "GET":
        pass

    elif request.method == "POST":
        action = request.form['action']
        if action == 'read_all':
            Notification.query.filter_by(member_id=current_user.id).update({'status': 1})
            db.session.commit()
            return jsonify({'status': 'success', 'message': 'Action success'})


@limiter.limit('4 per minute', error_message='You are rate limited. Try again after a minute')
@app.route('/user/withdraw', methods=['POST', 'GET'])
@login_required
@session_validate
def user_withdraw():
    if request.method == "GET":
        return render_template('account/withdraw.html', page='withdraw')

    elif request.method == "POST":
        form = WithdrawForm(request.form)
        deducts = {
            'earning': current_user.earning,
            'ref_earning': current_user.ref_earning,
            'all': current_user.earning + current_user.ref_earning
        }
        if form.validate():
            try:
                amt = Decimal(form.amt.data)
            except:
                raise 'Error'
            if form.deduct_from.data not in deducts:
                raise 'Error'

            bal = Decimal(deducts[form.deduct_from.data])
            if bal < amt:
                return jsonify({'status': 'error', 'message': 'Insufficient Balance'})

            if not valid_otp(form.twofa_code.data) and current_user.twofa_status:
                return jsonify({'status': 'error', 'message': 'Invalid Code'})
            acct_id = form.acct_id.data
            acct_result = PayoutAccount.query.filter_by(account_id=acct_id).first()
            if acct_result:
                # DEDUCT ACCORDINGLY
                bal -= amt
                if form.deduct_from.data == "earning":
                    current_user.earning = float(Decimal(current_user.earning) - amt)
                elif form.deduct_from.data == "ref_earning":
                    current_user.ref_earning = float(Decimal(current_user.ref_earning) - amt)
                else:
                    bal = Decimal(current_user.earning + current_user.ref_earning)
                    bal -= amt
                    current_user.earning, current_user.ref_earning = float(bal), 0

                # ADD TO HISTORY
                transaction = api.make_withdrawal(token='usdttrc', qty=float(amt)-WITHDRAWAL_FEE, addr=acct_result.wallet)
                if transaction['status'] == "completed":
                    new_history = ProgramHistory(member_id=current_user.id, name='withdraw', amt=float(amt),
                                                 method='usdt', time=get_timestamp(), wallet=acct_result.wallet,
                                                 fee=WITHDRAWAL_FEE, tx_id=transaction['id'], status=1)
                    db.session.add(new_history)
                    db.session.commit()
                    status = 'success'
                    message = 'Withdrawal success'
                else:
                    db.session.rollback()
                    new_history = ProgramHistory(member_id=current_user.id, name='withdraw', amt=float(amt),
                                                 method='usdt', time=get_timestamp(), wallet=acct_result.wallet, tx_id=generate_tx_id(), status=2)
                    db.session.add(new_history)
                    db.session.commit()
                    status = 'error'
                    message = transaction['message']

                return jsonify({'status': status, 'message': message, 'amt': f'{float(amt)-WITHDRAWAL_FEE:,.2f}'})
            return jsonify({'status': 'error', 'message': 'Account ID error'})
        return jsonify({'status': 'error', 'message': form.amt.errors or form.acct_id.errors or form.deduct_from.errors})


@app.route('/user/settings/withdraw', methods=['POST', 'GET'])
@login_required
@session_validate
def user_settings_withdraw():
    if request.method == "GET":
        return render_template('account/settings/withdraw.html', page='settings')

    elif request.method == "POST":
        form = AddWithdrawAccountForm(request.form)
        if form.validate():
            action = request.form['action']
            if action == 'add':
                if not re.match("^[T][a-km-zA-HJ-NP-Z1-9]{25,34}$", form.wallet.data):
                    return jsonify({'status': 'error', 'message': 'Invalid USDT TRC20 Wallet'})
                new_acct = PayoutAccount(member_id=current_user.id, token='usdt', wallet=form.wallet.data, time=get_timestamp(), account_id=generate_tx_id(4), label=form.name.data)
                db.session.add(new_acct)
                db.session.commit()
                return jsonify({'status': 'success', 'message': 'Wallet added successfully'})
        return jsonify({'status': 'error', 'message': form.wallet.errors or form.name.errors})


@app.route('/user/settings/general', methods=['POST', 'GET'])
@login_required
@session_validate
def user_settings_general():
    if request.method == "GET":
        # TWOFA
        if not current_user.twofa_secret:
            current_user.twofa_secret = pyotp.random_base32()
            uri = pyotp.totp.TOTP(current_user.twofa_secret).provisioning_uri(
                name=current_user.email,
                issuer_name=SITE_NAME)
            qr_path = f'static/images/twofa/{current_user.email}.png'
            qrcode.make(uri).save(qr_path)
            db.session.commit()
        return render_template('account/settings/general.html', page='settings')

    elif request.method == "POST":
        if request.form['action'] == "change_password":
            form = ChangePasswordForm(request.form)
            if form.validate():
                if form.new_pass.data == form.new_pass2.data:
                    if check_password_hash(current_user.password, form.old_pass.data):
                        if is_strong_password(form.new_pass.data)[0]:
                            if not check_password_hash(current_user.password, form.new_pass.data):
                                if current_user.twofa_status and valid_otp(form.twofa_code.data):
                                    current_user.password = generate_password_hash(form.new_pass.data)
                                    db.session.commit()
                                    return jsonify({'status': 'success', 'message': 'Password changed successfully'})
                                return jsonify({'status': 'error', 'message': 'Invalid 2FA code'})
                            return jsonify({'status': 'error', 'message': "Please choose a password you haven't used before"})
                        return jsonify(
                            {'status': 'error', 'message': is_strong_password(form.new_pass.data)[1]})
                    return jsonify({'status': 'error', 'message': "Current password is incorrect"})
                return jsonify({'status': 'error', 'message': "New passwords do not match"})
            return jsonify({'status': 'error', 'message': form.old_pass.errors or form.new_pass.errors})

        elif request.form['action'] == "enable_2fa":
            form = TwoFaForm(request.form)
            if form.validate():
                if not current_user.twofa_status:
                    if valid_otp(form.code.data):
                        current_user.twofa_status = True
                        db.session.commit()
                        return jsonify({'status': 'success', 'message': "2FA security activated successfully"})
                    return jsonify({'status': 'error', 'message': "Code is invalid"})
                return jsonify({'status': 'error', 'message': '2FA is already activated'})
            return jsonify({'status': 'error', 'message': form.code.errors})

        elif request.form['action'] == "disable_2fa":
            form = TwoFaForm(request.form)
            if form.validate():
                if current_user.twofa_status:
                    if valid_otp(form.code.data):
                        current_user.twofa_status = False
                        db.session.commit()
                        return jsonify({'status': 'success', 'message': "2FA security deactivated"})
                    return jsonify({'status': 'error', 'message': "Code is invalid"})
                return jsonify({'status': 'error', 'message': '2FA is not active on this account'})
            return jsonify({'status': 'error', 'message': form.code.errors})


@app.route('/promotion/watch/<target>', methods=['POST', 'GET'])
def home_videos_start(target):
    if request.method == "GET":
        try:
            target = str(uuid.UUID(target, version=4))
        except ValueError:
            abort(404)
        else:
            video = Video.query.filter_by(video_id=target).first()
            if not video:
                target = None
            elif video.status != 1:
                return redirect(f'{url_for("user_videos")}')
            return render_template('home/video/start.html', page='index', target=target, target_video=video)


@app.route('/promotion/video/<target>', methods=['POST', 'GET'])
def home_videos(target):
    if request.method == "GET":
        try:
            target = str(uuid.UUID(target, version=4))
        except ValueError:
            return render_template('account/videos.html', page='videos', target=None, target_video=None)

        video = Video.query.filter_by(video_id=target).first()
        if not video:
            target = None
        elif video.status != 1:
            return redirect(f'{url_for("user_videos")}')
        if current_user.is_authenticated or not video:
            return redirect(f'{url_for("user_videos")}?target={target}')
        else:
            return render_template('home/video/videos.html', page='videos', target=target, target_video=video)

    elif request.method == "POST":

        action = request.form.get('action')

        if action not in ['search', 'cat', 'reward']:
            raise "ERROR"

        page_token = request.form.get('page_token', 1, type=int)

        if action == "search":

            search_term = request.form.get('search_term')

            data = Video.query.filter(

                (Video.title.like(f'%{search_term}%') & Video.status == 1) |

                (Video.description.like(f'%{search_term}%') & Video.status == 1) |

                (Video.video_id.like(f'{search_term}') & Video.status == 1)

            ).order_by(func.random()).paginate(page=page_token, per_page=25, error_out=False)

        elif action == "cat":

            category = request.form.get('category', '')

            if category == "":

                data = Video.query.filter_by(status=1).order_by(func.random()).all()

            else:

                data = Video.query.filter_by(category=category, status=1).order_by(func.random()).paginate(
                    page=page_token, per_page=25)

        elif action == "reward":
            if request.cookies.get('bonus', None):
                return jsonify({'status': 'error', 'message': 'You have reached your max video limit, please create an account to continue'})
            device = request.user_agent.string

            video_id = request.form['videoId']
            video_result = Video.query.filter_by(video_id=video_id).first()

            # INCREMENT VIDEO COUNT
            video_result.count += 1

            # CHECK FOR CREATOR AND REWARD THEM IF ANY
            if video_result.creator:
                if video_result.user != current_user:
                    bonus = (VIDEO_CREATOR_BONUS / 100) * float(NEW_USER_VIDEO_REWARD)
                    video_result.user.earning += bonus
                    new_history = ProgramHistory(name='earning', amt=bonus, time=get_timestamp(),
                                                 tx_id=generate_tx_id(), member_id=video_result.user.id, status=1,
                                                 detail=f'video:{video_id}')
                    video_result.earning += bonus
                    db.session.add(new_history)

            new_visit = Visit(time=get_timestamp(), device=device)
            db.session.add(new_visit)
            db.session.commit()

            response = make_response(jsonify({'status': 'success', 'message': 'Success'}))
            response.set_cookie('bonus', 'set', max_age=(86400*14))
            return response

        if action == "search" or action == "cat":

            html_content = ''

            page_token += 1

            if data:

                status = "success"

                message = ""

                for video in data:
                    reward = PLANS[current_user.level]['profit'] / PLANS[current_user.level][
                        'videos'] if current_user.is_authenticated else NEW_USER_VIDEO_REWARD

                    html_content += f"""

                        <div class="mb-5 video-cards">

                            <div class="user-balance-card video-card-img" style="background: url('{url_for('static', filename='uploads/images/{}'.format(video.image_name))}'); background-position: center; background-size: cover;">
                            <!-- Play button -->
                            <div class="play-button special-bg open-bottom-modal watch-video" data-modal="watchModal" data-id="{video.video_id}" data-video_name="{video.video_name}" data-sec="{video.length}">
                                <i class="fas fa-play"></i>
                            </div>

                                <div class="wallet-name">

                                    <div class="default reward">{st(video.length, video=True)}</div>

                                    <div class="default2">${reward:,.2f}</div>

                                        </div>

                                    </div>

                                    <h6 class="title" style="overflow: hidden">{video.title}</h6>

                                    <div class="actions">

                                          <a href="#0" class="user-sidebar-btn open-bottom-modal video-details" data-id="{video.video_id}" data-modal="videoDetailModal" data-sec="{st(video.length, video=True)}" data-title="{video.title}" data-description="{video.description}" data-category="{video.category}" data-created="{format_date(video.time, '%d %b, %Y', 86400)}"><i

                                             class="anticon anticon-eye"></i>Video Details</a>

                                          <a href="#0" onclick="copy_data('http://{SITE_DOMAIN}{url_for('home_videos', target=video.video_id)}', 'Video link copied to clipboard!')" class="user-sidebar-btn red-btn share">

                                          <i class="anticon anticon-share-alt"></i>Share</a>

                                       </div>

                                    </div>

                        """


            else:

                status = "error"

                message = "No results found"

                for _ in range(25):
                    html_content += f"""

                    <div class="mb-5 video-cards">

                                   <div class="user-balance-card video-card-img" style="background-position: center; background-size: cover;">

                                      <div class="wallet-name">

                                         <div class="default reward">00:00</div>

                                          <div class="default2">$0.00</div>

                                      </div>

                                   </div>

                                  <h6 class="title"></h6>

                                   <div class="actions">

                                      <a href="#0" class="user-sidebar-btn"><i

                                         class="anticon anticon-eye"></i>Video Details</a>

                                      <a href="#0" class="user-sidebar-btn red-btn share"><i

                                         class="anticon anticon-share-alt"></i>Share</a>

                                   </div>

                                </div>

                    """

            return jsonify(
                {'status': status, 'html_content': html_content, "message": message, 'page_token': page_token})


@app.route('/user/videos', methods=['POST', 'GET'])
@login_required
@session_validate
def user_videos():
    if request.method == "GET":
        target = request.args.get('target')
        video = Video.query.filter_by(video_id=target).first()
        if not video:
            target = None
        elif video.status != 1:
            target = None
        return render_template('account/videos.html', page='videos', target=target, target_video=video)

    elif request.method == "POST":
        action = request.form.get('action')
        if action not in ['search', 'cat', 'reward', 'target']:
            raise "ERROR"
        page_token = request.form.get('page_token', 1, type=int)
        if action == "search":
            search_term = request.form.get('search_term')
            data = Video.query.filter(
                (Video.title.like(f'%{search_term}%') & Video.status == 1) |
                (Video.description.like(f'%{search_term}%') & Video.status == 1) |
                (Video.video_id.like(f'{search_term}') & Video.status == 1)
            ).order_by(func.random()).paginate(page=page_token, per_page=25, error_out=False)
        elif action == "cat":
            category = request.form.get('category', '')
            if category == "":
                data = Video.query.filter_by(status=1).order_by(func.random()).all()
            else:
                data = Video.query.filter_by(category=category, status=1).order_by(func.random()).paginate(page=page_token, per_page=25)
        elif action == "reward":
            if current_user.today_watch < 1:
                return jsonify({'status': 'error', 'message': 'Please come back tomorrow for more rewards'})

            reward = Decimal(request.form['reward'])
            video_id = request.form['videoId']

            video_result = Video.query.filter_by(video_id=video_id).first()
            if not video_result:
                return jsonify(
                    {'status': 'error', 'message': 'This video has been deleted by the author'})

            if video_result.video_id in [vv.vid_id for vv in current_user.video_views]:
                return jsonify({'status': 'error', 'message': 'You already earned on this video today. Come back tomorrow'})

            if current_user.level == 0:
                each_reward = Decimal(0.01)
            else:
                each_reward = Decimal(PLANS[current_user.level]['profit']/PLANS[current_user.level]['videos'])
            if reward > each_reward:
                reward = each_reward

            today_rewarded = False
            for history in current_user.history:
                if history.name == "earning" and not history.detail and dt.datetime.fromtimestamp(int(history.time)).date() == dt.datetime.now().date():
                    today_rewarded = True
                    if history.amt < PLANS[current_user.level]['profit']:
                        history.amt += float(reward)
                        history.time = get_timestamp()
                        history.stack += 1
            if not today_rewarded:
                new_history = ProgramHistory(name='earning', amt=float(reward), time=get_timestamp(), tx_id=generate_tx_id(), member_id=current_user.id, status=1)
                db.session.add(new_history)
            current_user.earning += float(reward)
            current_user.today_watch -= 1

            # ADD VIDEO VIEW
            new_video_view = VideoWatched(member_id=current_user.id, vid_id=video_id, time=get_timestamp())
            db.session.add(new_video_view)

            # INCREMENT VIDEO COUNT
            video_result.count += 1

            # CHECK FOR CREATOR AND REWARD THEM IF ANY
            if video_result.creator:
                if video_result.user != current_user:
                    bonus = (VIDEO_CREATOR_BONUS/100) * float(reward)
                    video_result.user.earning += bonus

                    # CHECK IF CREATOR HAS BEEN REWARDED FOR THE DAY AND ADD TO THE DAYS HISTORY
                    today_rewarded = False
                    for history in video_result.user.history:
                        if history.name == "earning" and history.detail and dt.datetime.fromtimestamp(
                                int(history.time)).date() == dt.datetime.now().date():
                            today_rewarded = True
                            history.amt += bonus
                            history.time = get_timestamp()
                            history.stack += 1
                    if not today_rewarded:
                        new_history = ProgramHistory(name='earning', amt=bonus, time=get_timestamp(),
                                                     tx_id=generate_tx_id(), member_id=video_result.user.id, status=1,
                                                     detail=f'video:{video_id}')
                        db.session.add(new_history)
            db.session.commit()
            return jsonify({'status': 'success', "message": 'Reward computed successfully'})

        if action == "search" or action == "cat":
            html_content = ''
            page_token += 1
            if data:
                status = "success"
                message = ""
                for video in data:
                    reward = PLANS[current_user.level]['profit'] / PLANS[current_user.level]['videos'] if current_user.is_authenticated else NEW_USER_VIDEO_REWARD
                    html_content += f"""
                        <div class="mb-5 video-cards">
                            <div class="user-balance-card video-card-img" style="background: url('{url_for('static', filename='uploads/images/{}'.format(video.image_name))}'); background-position: center; background-size: cover;">
                            <!-- Play button -->
                            <div class="play-button special-bg open-bottom-modal watch-video" data-modal="watchModal" data-id="{video.video_id}" data-video_name="{video.video_name}" data-sec="{video.length}">
                                <i class="fas fa-play"></i>
                            </div>
                            
                                <div class="wallet-name">
                                    <div class="default reward">{st(video.length, video=True)}</div>
                                    <div class="default2">${reward:,.2f}</div>
                                        </div>
                                    </div>
                                    <h6 class="title" style="overflow: hidden">{video.title}</h6>
                                    <div class="actions">
                                          <a href="#0" class="user-sidebar-btn open-bottom-modal video-details" data-id="{video.video_id}" data-modal="videoDetailModal" data-sec="{st(video.length, video=True)}" data-title="{video.title}" data-description="{video.description}" data-category="{video.category}" data-created="{format_date(video.time, '%d %b, %Y', 86400)}"><i
                                             class="anticon anticon-eye"></i>Video Details</a>
                                          <a href="#0" onclick="copy_data('http://{SITE_DOMAIN}{url_for('home_videos_start', target=video.video_id)}', 'Video link copied to clipboard!')" class="user-sidebar-btn red-btn share">
                                          <i class="anticon anticon-share-alt"></i>Share</a>
                                       </div>
                                    </div>
                        """

            else:
                status = "error"
                message = "No results found"
                for _ in range(25):
                    html_content += f"""
                    <div class="mb-5 video-cards">
                                   <div class="user-balance-card video-card-img" style="background-position: center; background-size: cover;">
                                      <div class="wallet-name">
                                         <div class="default reward">00:00</div>
                                          <div class="default2">$0.00</div>
                                      </div>
                                   </div>
                                  <h6 class="title"></h6>
                                   <div class="actions">
                                      <a href="#0" class="user-sidebar-btn"><i
                                         class="anticon anticon-eye"></i>Video Details</a>
                                      <a href="#0" class="user-sidebar-btn red-btn share"><i
                                         class="anticon anticon-share-alt"></i>Share</a>
                                   </div>
                                </div>
                    """

            return jsonify({'status': status, 'html_content': html_content, "message": message, 'page_token': page_token})


@app.route('/user/transactions', methods=['POST', 'GET'])
@login_required
@session_validate
def user_transactions():
    if request.method == 'GET':
        return render_template('account/transactions.html', page='transactions', name='all')
    elif request.method == 'POST':
        action = request.form['action']
        if action not in ['fetch']:
            raise "ERROR"

        if action == "fetch":
            name = request.form['name']
            q = request.form['q']
            page = request.form.get('page', type=int)
            page += 1
            expected = {'all': 'All Transactions', 'earning': 'Video Earnings', 'ref_earning': 'Referral Bonuses', 'deposit': 'Subscriptions', 'withdraw': 'Payouts', 'search': 'Search Result'}
            if name in expected:
                data = ""
                if q != "":
                    result = ProgramHistory.query.filter(ProgramHistory.member_id == current_user.id, ProgramHistory.tx_id.like('%' + q+ '%')).order_by(ProgramHistory.id.desc()).paginate(page=page, per_page=TX_PER_PAGE)
                else:
                    if name == "all":
                        result = ProgramHistory.query.filter_by(member_id=current_user.id).order_by(ProgramHistory.id.desc()).paginate(page=page, per_page=TX_PER_PAGE)
                    else:
                        result = ProgramHistory.query.filter(ProgramHistory.member_id == current_user.id, ProgramHistory.name == name).order_by(ProgramHistory.id.desc()).paginate(page=page, per_page=TX_PER_PAGE)

                if result.items:
                    for history in result.items:
                        sign = "+"
                        fee_class = "d-none"
                        name_class = "green"
                        name_text = ""
                        if history.name == "withdraw":
                            name_text = "Payout"
                            sign = "-"
                            fee_class = ""
                            name_class = "red"
                        elif history.name == "deposit":
                            name_text = "Subscription"
                        elif history.name == "earning":
                            name_text = "Earning" if history.stack == 1 else f"Earning <span class='yellow-color'>x{history.stack}</span>"

                            if history.detail and 'video' in history.detail:
                                name_text = "Creator Earning" if history.stack == 1 else f"Creator Earning <span class='yellow-color'>x{history.stack}</span>"
                        elif history.name == "ref_earning":
                            name_text = "Referral Bonus"

                        if history.status == 0:
                            status_text = "Processing"
                            status_class = "pending"
                        elif history.status == 1:
                            status_text = "Completed"
                            status_class = "success"
                        else:
                            status_text = "Failed"
                            status_class = "failed"

                        data += f"""
                            
                            <div class="single-transaction">
                                <div class="transaction-left">
                                    <div class="transaction-des">
                                        <div class="fw-bold {name_class}-color">
                                            {name_text}
                                        </div>
                                        <div class="transaction-id ">{history.tx_id}</div>
                                        <div class="transaction-date">
                                            {format_date(history.time, '%d %b %H:%M', 86400)}
                                        </div>
                                    </div>
                                </div>
                                <div class="transaction-right">
                                    <div class="transaction-amount ">
                                        {sign}{history.amt:,.2f} USD
                                    </div>
                                    <div class="transaction-fee sub {fee_class}">-{history.fee:,.1f} USD Fee </div>
        
                                    <div class="transaction-status site-badge badge-{status_class}">{status_text}</div>
                                </div>
                            </div>
                            
                            """
                else:
                    data = f"""
                            <div style="text-align: center !important;">
                                <img style="width: 30%; display: inline-block;" src="{url_for('static', filename='images/none.png')}" alt="">
                            </div>
                        """

                if result.page == result.pages or len(result.items) == 0:
                    load_more = "none"
                else:
                    load_more = "inline-block"
                return jsonify({'status': 'success', 'content': data, 'header': expected[name], 'page': page, 'count_details': f'{((page-1)*TX_PER_PAGE)+len(result.items)} of {result.total}', 'load_more': load_more})
            else:
                raise "ERROR"


@app.route('/user/transactions/<name>', methods=['GET'])
@login_required
@session_validate
def user_transactions_name(name):
    if name in ['all', 'earning', 'ref_earning', 'deposit', 'withdraw']:
        if request.method == 'GET':
            return render_template('account/transactions.html', page='transactions', name=name)
    abort(404)


@app.route('/user/referral', methods=['GET', 'POST'])
@login_required
@session_validate
def user_referral():
    if request.method == 'GET':
        upline = Member.query.filter_by(id=int(current_user.upline)).first() if current_user.upline != "" else None
        return render_template('account/referral.html', page='referral', upline=upline)
    elif request.method == 'POST':
        action = request.form['action']
        level = request.form.get('level', type=int)
        if action == 'fetch':
            q = request.form['q']
            id_ = request.form.get('id', current_user.code)
            result = Member.query.filter_by(code=id_).first() or current_user.code
            downlines = get_downlines(result, level, q=q if q != "" else None)
            return jsonify({'status': 'success', 'level': downlines['level'], 'tree': downlines['tree'], 'bonus': f"{downlines['bonus']:,.2f}", 'links': f'{downlines["links"]}'})

        elif action == "fetch_log":
            page = request.form.get('page', type=int)
            page += 1

            data = ""
            total_bonus = 0
            result = (ProgramHistory.query.filter(
                ProgramHistory.member_id == current_user.id,
                ProgramHistory.name == 'ref_earning')
                      .order_by(ProgramHistory.id.desc()).paginate(page=page, per_page=TX_PER_PAGE))

            if result.items:
                for history in result.items:
                    sign = "+"
                    name_class = "green"
                    name_text = "Referral Bonus"

                    if history.status == 0:
                        status_class = "pending"
                    elif history.status == 1:
                        status_class = "success"
                    else:
                        status_class = "failed"

                    downline_result = Member.query.filter_by(id=int(history.detail)).first()
                    total_bonus += history.amt

                    data += f"""

                            <div class="single-transaction">
                                <div class="transaction-left">
                                    <div class="transaction-des">
                                        <div class="fw-bold {name_class}-color">
                                            {name_text}
                                        </div>
                                        <div class="transaction-id ">{history.tx_id}</div>
                                        <div class="transaction-date">
                                            {format_date(history.time, '%d %b %H:%M', 86400)}
                                        </div>
                                    </div>
                                </div>
                                <div class="transaction-right">
                                    <div class="transaction-amount ">
                                        {sign} ${history.amt:,.2f}
                                    </div>
                                    <div class="transaction-status site-badge badge-{status_class}">{hash_string(downline_result.email.split('@')[0])}</div>
                                    <div class="transaction-status site-badge badge-pending">Level {history.label}</div>
                                </div>
                            </div>

                    """
            else:
                data = f"""
                            <div style="text-align: center !important;">
                                <img style="width: 30%; display: inline-block;" src="{url_for('static', filename='images/none.png')}" alt="">
                            </div>
                        """

            if result.page == result.pages or len(result.items) == 0:
                load_more = "none"
            else:
                load_more = "inline-block"
            return jsonify({'status': 'success', 'content': data, 'page': page,
                            'count_details': f'{((page - 1) * TX_PER_PAGE) + len(result.items)} of {result.total}',
                            'load_more': load_more, 'total_bonus': f'{total_bonus:,.2f}'})

        else:
            raise "ERROR"


@app.route('/user/creator', methods=['GET', 'POST'])
@login_required
@session_validate
def user_creator():
    if request.method == 'GET':
        return render_template('account/creator.html', page='creator')
    elif request.method == 'POST':
        video_id = request.form['video_id']
        result = Video.query.filter_by(video_id=video_id).first()
        if result:
            video_path = os.path.join(app.config['VIDEO_UPLOAD_FOLDER'], result.video_name)
            image_path = os.path.join(app.config['IMAGE_UPLOAD_FOLDER'], result.image_name)
            try:
                os.remove(video_path)
                os.remove(image_path)
                db.session.delete(result)
                db.session.commit()
                return jsonify({'status': 'success', 'message': 'Content deleted successfully'})
            except OSError:
                return jsonify({'status': 'error', 'message': f'Error deleting content'})
        return jsonify({'status': 'error', 'message': 'Content does not exist'})


@app.route('/user/create', methods=['POST', 'GET'])
@login_required
@session_validate
def user_create():
    if request.method == 'GET':
        form = CreateVideoForm()
        return render_template('account/create.html', page='create', video=None, form=form)
    elif request.method == "POST":
        form = CreateVideoForm(request.form)
        if form.validate_on_submit():
            if 'video' in request.files and 'cover' in request.files:
                title = form.title.data
                category = form.category.data
                description = form.description.data
                cover = request.files.get('cover')
                video = request.files.get('video')

                if cover and allowed_file(cover.filename, 'image'):
                    if video and allowed_file(video.filename, 'video'):
                        video_size = len(video.read())
                        video.seek(0)  # Reset file pointer for video

                        cover_size = len(cover.read())
                        cover.seek(0)  # Reset file pointer for cover

                        if video_size <= 150 * 1024 * 1024:
                            if cover_size <= 3 * 1024 * 1024:
                                # Processing logic here
                                video_id = str(uuid.uuid4())
                                new_video_name = f'{video_id}.{video.filename.rsplit(".", 1)[1]}'
                                new_cover_name = f'{video_id}.{cover.filename.rsplit(".", 1)[1]}'
                                video.save(os.path.join(app.config['VIDEO_UPLOAD_FOLDER'], new_video_name))
                                cover.save(os.path.join(app.config['IMAGE_UPLOAD_FOLDER'], new_cover_name))

                                # Get the duration of the video using moviepy
                                clip = VideoFileClip(os.path.join(app.config['VIDEO_UPLOAD_FOLDER'], new_video_name))
                                duration = clip.duration if clip.duration >= 60 else 60
                                new_video = Video(video_id=video_id, category=category, title=title, description=description, creator=current_user.id, length=duration, time=get_timestamp(), status=0, image_name=new_cover_name, video_name=new_video_name)
                                db.session.add(new_video)
                                db.session.commit()
                                return jsonify({'status': 'success', 'message': 'Success', 'type': 'create'})
                            else:
                                return jsonify({'status': 'error', 'message': 'Max cover image size is 3 MB'})
                        else:
                            return jsonify({'status': 'error', 'message': 'Max video file size is 150 MB'})
                    else:
                        return jsonify(
                            {'status': 'error', 'message': 'Only .MP4, .MKV, and .AVI video files are allowed'})
                else:
                    return jsonify({'status': 'error', 'message': 'Only .JPG, .JPEG, and .PNG image files are allowed'})
            else:
                return jsonify({'status': 'error', 'message': 'Both video and cover image are required'})

        return jsonify(
            {'status': 'error', 'message': form.title.errors or form.description.errors or form.category.errors})

        # return jsonify({'status': 'error', 'message': ''})


def grab_save_yt_video(url, category, yt_id):

    try:
        yt = YouTube(url)
        filtered_streams = yt.streams.filter(res='720p', file_extension='mp4')
        video_stream = filtered_streams.first()
        duration = yt.length
        if video_stream and duration <= 60:
            title = yt.title
            description = yt.description

            image_url = yt.thumbnail_url
            video_id = str(uuid.uuid4())
            video_stream.download(app.config['VIDEO_UPLOAD_FOLDER'], filename=f'{video_id}.mp4')
            new_video_name = f'{video_id}.mp4'

            cover = requests.get(image_url)
            cover_ext = image_url.rsplit('.', 1)[1].split('?')[0]
            new_cover_name = f'{video_id}.{cover_ext}'
            cover_file = os.path.join(app.config['IMAGE_UPLOAD_FOLDER'], f'{video_id}.{cover_ext}')
            with open(cover_file, 'wb') as file:
                file.write(cover.content)

            new_video = Video(video_id=video_id, category=category, title=title,
                              description=description, length=duration,
                              time='1704063600', status=1, image_name=new_cover_name,
                              video_name=new_video_name, yt_id=yt_id, creator=current_user.id)
            db.session.add(new_video)
            db.session.commit()
            return {'status': 'success', 'message': 'Success'}
        else:
            return {'status': 'error', 'message': 'Success'}
    except RegexMatchError:
        return {'status': 'error', 'message': 'Invalid YouTube link format'}
    except VideoUnavailable:
        return {'status': 'error', 'message': 'Video is unavailable'}
    except PytubeError as e:
        return {'status': 'error', 'message': f'Error: {str(e)}'}


@app.route('/user/create2', methods=['POST', 'GET'])
@login_required
@session_validate
def user_create2():
    """
    UPLOADING VIDEOS USING URL AND CATEGORY
    """
    if request.method == 'GET':
        form = CreateVideoForm()
        return render_template('account/create2.html', page='create', video=None, form=form)
    elif request.method == "POST":
        url = request.form['url']
        category = request.form['category']

        # GET VIDEO DETAILS FROM PYTUBE
        try:
            yt = YouTube(url)
            filtered_streams = yt.streams.filter(res='720p', file_extension='mp4')
            video_stream = filtered_streams.first()
            title = yt.title
            description = yt.description
            duration = yt.length
            size_mb = video_stream.filesize_mb
            image_url = yt.thumbnail_url
            if size_mb <= 150:
                video_id = str(uuid.uuid4())
                video_stream.download(app.config['VIDEO_UPLOAD_FOLDER'], filename=f'{video_id}.mp4')
                new_video_name = f'{video_id}.mp4'

                cover = requests.get(image_url)
                cover_ext = image_url.rsplit('.', 1)[1].split('?')[0]
                new_cover_name = f'{video_id}.{cover_ext}'
                cover_file = os.path.join(app.config['IMAGE_UPLOAD_FOLDER'], f'{video_id}.{cover_ext}')
                with open(cover_file, 'wb') as file:
                    file.write(cover.content)

                if duration < 60:
                    duration = 60
                new_video = Video(video_id=video_id, category=category, title=title,
                                  description=description, creator=current_user.id, length=duration,
                                  time=get_timestamp(), status=1, image_name=new_cover_name,
                                  video_name=new_video_name)
                db.session.add(new_video)
                db.session.commit()
                return jsonify({'status': 'success', 'message': 'Success', 'type': 'create'})
            else:
                return jsonify({'status': 'error', 'message': 'Video size is over 150 MB'})
        except RegexMatchError:
            return jsonify({'status': 'error', 'message': 'Invalid YouTube link format'})
        except VideoUnavailable:
            return jsonify({'status': 'error', 'message': 'Video is unavailable'})
        except PytubeError as e:
            return jsonify({'status': 'error', 'message': f'Error: {str(e)}'})


@app.route('/user/create3', methods=['POST', 'GET'])
@login_required
@session_validate
def user_create3():
    """
    UPLOADING MULTIPLE VIDEOS AT A TIME USING JUST CATEGORY AND NUMBER_OF_VIDEOS
    """
    if request.method == 'GET':
        form = CreateVideoForm()
        return render_template('account/create3.html', page='create', video=None, form=form)
    elif request.method == "POST":
        count = request.form.get('count', type=int)
        category = request.form['category']
        success = 0

        data = api.search_videos(category)
        for video_id, details in data.items():
            if not Video.query.filter_by(yt_id=video_id).first() and success < count:
                url = f'https://www.youtube.com/watch?v={video_id}'
                response = grab_save_yt_video(url, category, video_id)
                if response['status'] == "success":
                    success += 1
            elif success >= count:
                break
        return jsonify({'status': 'success', 'success_count': success})


@app.route('/user/create/<video_id>', methods=['POST', 'GET'])
@login_required
@session_validate
def user_create_patch(video_id):
    video = Video.query.filter_by(video_id=video_id).first()
    if not video:
        abort(404, "Video not found")
    if request.method == 'GET':
        return render_template('account/create.html', page='create', video=video)
    elif request.method == "POST":
        form = CreateVideoForm(request.form)
        if form.validate_on_submit():
            title = form.title.data
            category = form.category.data
            description = form.description.data

            video.title = title
            video.category = category
            video.description = description
            video.modified = get_timestamp()
            video.status = 0
            db.session.commit()
            return jsonify({'status': 'success', 'message': 'success', 'type': 'modify'})

        return jsonify(
            {'status': 'error', 'message': form.title.errors or form.description.errors or form.category.errors})


# ADMIN PANEL >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>


def load_member(username):
    return db.session.query(Member).filter(Member.email == username).first()


def sql_to_dict(obj):
    our_dict = {}
    for col in obj.__table__.columns:
        our_dict[col.name] = getattr(obj, col.name)
    return our_dict


def get_status_details(status: int, **kwargs) -> list:
    """

    :param status: status integer
    :return: returns list [status_class, status_text]
    """
    if status == 0:
        status_class = "warning"
        status_text = kwargs.get('zero', 'pending')
    elif status == 1:
        status_class = "success"
        status_text = kwargs.get('one', 'completed')
    else:
        status_class = "danger"
        status_text = kwargs.get('two', 'failed')
    return [status_class, status_text]


@app.route('/admin/')
def admin_index():
    return redirect(url_for('admin_login'))


@app.route('/admin/login', methods=['POST', 'GET'])
def admin_login():
    form = AdminLoginForm()
    if form.validate_on_submit():
        result = db.session.query(AdminMember).filter(AdminMember.email == form.email.data).first()
        if result is not None:
            if check_password_hash(result.password, form.password.data):
                session['role'] = 'admin'
                login_user(result)
                time = int(dt.datetime.now().timestamp())
                if result.logins is None:
                    result.logins = f'{time}={time}'
                else:
                    time1, time2 = result.logins.split("=")
                    result.logins = f'{time1}={time}'
                db.session.commit()
                next_ = request.args.get('next')
                return redirect(next_ or url_for('admin_dashboard_page'))
        form.password.errors = "Invalid login credentials"
    return render_template('admin/login.html', form=form)


@app.route('/admin/registerrrr', methods=['POST', 'GET'])
def admin_register():
    form = AdminRegisterForm()
    if form.validate_on_submit():
        result = db.session.query(AdminMember).filter(AdminMember.email == form.email.data).first()
        if result is None:
            new_record = AdminMember(
                email=form.email.data,
                fname=form.fname.data,
                password=generate_password_hash(form.password.data),
                role=form.role.data
            )

            db.session.bulk_save_objects([new_record])
            db.session.commit()
            form.password.errors = "User has been added successfully"
        else:
            form.password.errors = "Email already exists"
    return render_template('admin/register.html', form=form)


@app.route('/admin/dashboard')
@login_required
def admin_dashboard_page():
    members = db.session.query(Member).all()
    return render_template('admin/dashboard.html', members=members)


@app.route('/admin/members')
@login_required
def admin_members_page():
    members = db.session.query(Member).all()
    return render_template('admin/members.html', members=members)


@app.route('/admin/deposit')
@login_required
def admin_deposit_page():
    return render_template('admin/deposit.html')


@app.route('/admin/withdraw')
@login_required
def admin_withdraw_page():
    return render_template('admin/withdraw.html')


@app.route('/admin/invest')
@login_required
def admin_invest_page():
    return render_template('admin/invest.html')


@app.route('/admin/agents')
@login_required
def admin_agents_page():
    return render_template('admin/agents.html')


@app.route('/admin/get_signup_history', methods=['POST'])
@login_required
def admin_get_signup_history():
    results = db.session.query(Member).order_by(Member.id.desc()).limit(20).all()

    content = ''
    for user in results:
        if current_user.has_access_to(user):

            content += f"""
            <tr data-code='{user.email}' data-fullname='{user.fullname}' data-email='{user.email}' data-reg_date='{format_date(user.reg_date, "%Y-%m-%dT%H:%M")}' data-registered='{st(user.reg_date)} ago' data-code='{user.email}' class='trs' data-bs-toggle='modal' data-bs-target='#exampleLargeModal1' >
					<td>
						<div class='d-flex align-items-center'>
							<div class='ms-2'>
								<h6 class='mb-1 font-14'>{user.email.split('@')[0]}</h6>					
								</div>
							</div>
						</td>
						<td>{st(user.reg_date)} ago</td>												
					</tr>
            """
    if content == "":
        content = '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty">No data available!</td></tr>'

    return jsonify({'content': content})


@app.route('/admin/logout', methods=['GET'])
@login_required
def admin_logout():
    logout_user()
    return redirect(url_for('admin_login'))


@app.route('/admin/login_as', methods=['POST'])
@login_required
def admin_login_as():
    user = request.form.get('username')
    # current_user.record_action('login_as', user)
    return 'success'


@app.route('/admin/get_user_trans', methods=['POST'])
@login_required
def admin_get_user_trans():
    user = db.session.query(Member).filter(Member.email == request.form.get('username')).first()

    content = """
    <li _ngcontent-ehg-c192="" class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="font-weight: bold !important">
                              <h6 _ngcontent-ehg-c192="" class="mb-0" style="font-weight: bold !important">
                                 Order
                              </h6>
                              <span _ngcontent-ehg-c192="" class="text-secondary">Amount</span>
                              <span _ngcontent-ehg-c192="" class="text-secondary">Date</span>
                              <span _ngcontent-ehg-c192="" class="text-secondary">Status</span>
                           </li>
                           """
    if user.program_history:
        for x in range(len(user.program_history[:8]) - 1, -1, -1):
            row = user.program_history[x]
            status_class, status_text = get_status_details(row.status)
            content += f"""
            <li _ngcontent-ehg-c192="" class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                             <span _ngcontent-ehg-c192="" class="text-secondary" style="text-transform: capitalize">{row.name}</span>
                              <span _ngcontent-ehg-c192="" class="text-secondary">${row.amt:,}</span>
                              <span _ngcontent-ehg-c192="" class="text-secondary">{format_date(row.time, '%d %b')}</span>
                              <span style="text-transform: uppercase; font-size: .58em !important" class="badge rounded-pill text-bg-{status_class}" >{status_text}</span>
                           </li>
            """
    else:
        content += """
        <li _ngcontent-ehg-c192="" class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                              <h6 _ngcontent-ehg-c192="" class="mb-0">
                                 No 
                              </h6>
                              <span _ngcontent-ehg-c192="" class="text-secondary">transactions</span>
                              <span _ngcontent-ehg-c192="" class="text-secondary">found!</span>
                           </li>
        """
    return jsonify({'err': 0, 'return': content})


@app.route('/admin/top_actions', methods=['POST'])
@login_required
def admin_top_actions():
    user = load_member(request.form.get('username'))
    action = request.form.get('action')
    msg = ''
    if action == "ban":
        if not current_user.privilege.ban and current_user.role != "admin":
            # return jsonify({'err': 1, 'msg': 'No privileges'})
            return jsonify({'err': 1, 'msg': 'No privileges'})
        if user.suspend:
            user.suspend = False
            msg = "User unbanned successfully"
            current_user.record_action('unban', user.email)
        else:
            user.suspend = True
            msg = "User banned successfully"
            current_user.record_action('ban', user.email)
    elif action == "erase":
        if not current_user.privilege.erase and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        db.session.delete(user)
        msg = "User is cleared successfully"
        current_user.record_action('erase', user.email)
    elif action == "approve_doc":
        if not current_user.privilege.docs and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        user.file_status = 2
        msg = "Account verified successfully"
        current_user.record_action('docs_approve', user.email)
        if user.file_status != 2:
            # EmailSender('docs_approve', email=user.email).send_email()
            pass
    elif action == "reject_doc":
        if not current_user.privilege.docs and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        user.file_status = 0
        msg = "Document rejected successfully!"
        current_user.record_action('docs_reject', user.email)
        if user.file_status != 0:
            # EmailSender('docs_reject', email=user.email).send_email()
            pass

    return jsonify({'err': 0, 'msg': msg})


@app.route('/admin/save_actions', methods=['POST'])
@login_required
def admin_save_actions():
    user = load_member(request.form.get('username'))
    table = request.form.get('table')
    id_ = request.form.get('id')
    value = request.form.get('value')
    key = request.form.get('key')
    msg = ''
    dest_dict = {}
    if key == "fullnamee":
        key = "fullname"
    if key == "reg_date" or key == "dob" or key == "time":
        value = int(dt.datetime.strptime(value, "%Y-%m-%dT%H:%M").timestamp())
    else:
        try:
            if key != "phone":
                value = float(value)
        except ValueError:
            if key == "total_deposit" or key == "program_cash":
                value = value.replace('$', '').replace(',', '')

    if table == "members":
        member_obj = db.session.query(Member).filter_by(id=id_).first()
        if member_obj is not None:
            if not current_user.privilege.edit_profile and current_user.role != "admin":
                return jsonify({'err': 1, 'msg': 'No privileges'})
            db.session.query(Member).filter_by(id=id_).update({key: value})
            dest_dict = sql_to_dict(member_obj)
            print(dest_dict)
    elif table == "program_history":
        order_obj = db.session.query(ProgramHistory).filter_by(id=id_).first()
        if order_obj is not None:
            if not current_user.privilege.edit_wallet and current_user.role != "admin":
                return jsonify({'err': 1, 'msg': 'No privileges'})
            db.session.query(ProgramHistory).filter_by(id=id_).update({key: value})
            dest_dict = sql_to_dict(order_obj)
    # elif table == "investments":
    #     order_obj = db.session.query(Investment).filter_by(id=id_).first()
    #     if order_obj is not None:
    #         if not current_user.privilege.trade_values and current_user.role != "admin":
    #             return jsonify({'err': 1, 'msg': 'Trade values privilege is required'})
    #         db.session.query(Investment).filter_by(id=id_).update({key: value})
    #         dest_dict = sql_to_dict(order_obj)
    # if dest_dict[key] != value:
    #     msg = f'{key} updated successfully'
    #     current_user.record_action('edit_profile', user.email, key=key, former_value=dest_dict[key], value=value,
    #                                table=table, id=id_)

    return jsonify({'err': 0, 'msg': msg})


def create_deposit_table(row, content, sn):
    sn += 1
    status_class, status_text = get_status_details(row.status)

    content += f"""
                    <tr class='deposit_trs'>
                        <td>{sn}</td>
                        <td>{row.user.email}</td>
                        <td>${row.amt:,.2f}</td>
                        <td style='text-transform: uppercase'>{row.method}</td>
                        <td>{format_date(int(row.time), '%d %b %H:%M')}</td>
                        <td>
                            <div class='badge rounded-pill w-100 text-bg-{status_class}'>{status_text}</div>
                        </td>
                    """

    return [content, sn]


def create_tf_table(row, content, sn, by):
    sn += 1
    status_class, status_text = get_status_details(row.status)

    if len(row.detail) > 5:
        if row.detail == by:
            from_ = row.user.email.split("@")[0]
            to = 'Self'
            sub2 = f'A user, {row.user.email.split("@")[0]} made a transfer into this account'
        else:
            from_ = 'Self'
            to = row.detail.split("@")[0]
            sub2 = f'This account initiated a transfer to {row.detail.split("@")[0]}'

    elif len(row.detail) <= 5:
        if row.detail == 'spot':
            from_ = 'BAL wallet'
            to = 'SPOT wallet'
            sub2 = 'Transfer was initiated from BAL wallet to SPOT wallet'
        else:
            from_ = 'SPOT wallet'
            to = 'BAL wallet'
            sub2 = 'Transfer was initiated from SPOT wallet to BAL wallet'
    else:
        from_ = ''
        to = ''
        sub2 = ''

    content += f"""
                    <tr data-code='{row.time}' data-amt='{row.qty:,.4f} {row.method.upper()} (${row.amt:,.2f})' data-detail='{sub2}' data-username='{row.user.email}' data-date='{format_date(row.time, "%Y-%m-%dT%H:%M")}' data-id='{row.id}' data-method='{row.method}' data-status='{status_text}' class='tf_trs'>
                                                        <td>
                                                        {sn}
                                                        </td>
                                                        <td>{by.split('@')[0]}</td>
                                                        <td>{row.qty:,.4f} {row.method.upper()}</td>
                                                        <td>{from_}</td>
                                                        <td>{to}</td>
                                                        <td>{format_date(int(row.time), '%d %b %H:%M')}</td>

                                                        <td data-bs-toggle='modal' data-bs-target='#TfModal2' style='text-decoration: underline; color: blue'>view detail</td>
                                                    <td>
                                                    <div class=dropdown ms-auto>
                                                    <div data-bs-toggle='dropdown' class='dropdown-toggle dropdown-toggle-nocaret cursor-pointer' aria-expanded='false'>
                                                        <i class='bx bx-dots-vertical-rounded font-22'></i>
                                                    </div>
                                                    <ul class='dropdown-menu' style='cursor:pointer'>
                                                    <li data-id='{row.id}' data-action='erase' class='tf_actions'><a class='dropdown-item' style='color: red; font-weight: bold'><i class='bx bx-trash font-22'></i> Erase</a></li>
                                                    </ul>
                                                    </div>
                                                </td>
                                                    </tr>
                    """

    return [content, sn]


@app.route('/admin/get_deposit_history', methods=['POST'])
@login_required
def admin_get_deposit_history():
    programs = db.session.query(ProgramHistory).filter_by(name="deposit").order_by(ProgramHistory.id.desc()).all()
    content = ''
    sn = 0
    for program in programs:
        if current_user.has_access_to(program.user):
            content, sn = create_deposit_table(program, content, sn)
    return content


@app.route('/admin/get_tf_history', methods=['POST'])
@login_required
def admin_get_tf_history():
    programs = db.session.query(ProgramHistory).filter_by(name="transfer").order_by(ProgramHistory.id.desc()).all()
    content = ''
    sn = 0
    for program in programs:
        if current_user.has_access_to(program.user):
            content, sn = create_tf_table(program, content, sn, program.user.email)
    return content


@app.route('/admin/get_tf_history2', methods=['POST'])
@login_required
def admin_get_tf_history2():
    user = db.session.query(Member).filter_by(email=request.form.get('username')).first()
    content = ''
    sn = 0
    if current_user.has_access_to(user) and user.program_history:
        for x in range(len(user.program_history) - 1, -1, -1):
            row = user.program_history[x]
            if row.name == "transfer":
                content, sn = create_tf_table(row, content, sn, row.user.email)
    return content


def create_with_table(row, content, sn):
    sn += 1
    status_class, status_text = get_status_details(row.status)

    content += f"""
                    <tr class='with_trs'>
                        <td>
    						<div class='d-flex align-items-center'>
    							<div class='ms-2'>
    								<h6 class='mb-0 font-14'>{sn}</h6>
    							</div>
    						</div>
    					</td>
    					<td>{row.user.email}</td>		
    					<td>${row.amt:,}</td>
    					<td>{row.method.upper()}</td>
    					<td>{row.wallet}</td>
    					<td>{format_date(row.time, '%d %b %H:%M')}</td>
    					<td>
    						<div class='badge rounded-pill w-100 text-bg-{status_class}'>{status_text}</div>
    					</td>
                    """
    return [content, sn]


@app.route('/admin/get_with_history', methods=['POST'])
@login_required
def admin_get_with_history():
    programs = db.session.query(ProgramHistory).filter_by(name="withdraw").order_by(ProgramHistory.id.desc()).all()
    content = ''
    sn = 0
    for program in programs:
        if current_user.has_access_to(program.user):
            content, sn = create_with_table(program, content, sn)
    return content


@app.route('/admin/deposit_actions', methods=['POST'])
@login_required
def admin_deposit_actions():
    action = request.form.get('action')
    id_ = request.form.get('id')
    order = db.session.query(ProgramHistory).filter_by(id=id_).first()
    if order is None:
        return jsonify({'err': 1, 'msg': 'This order does not exist!'})
    if action == "confirm":
        if not current_user.privilege.deposit_approve and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        if order.method == "btc":
            order.user.balance_wallet.btc += order.qty
        elif order.method == "eth":
            order.user.balance_wallet.eth += order.qty
        else:
            order.user.balance_wallet.btc += order.qty
        order.user.total_deposit += order.amt
        order.status = 1
        current_user.record_action('approve_deposit', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        # EmailSender('approve_deposit', id=order.time, email=order.user.email, method=order.method.upper(),
        #             qty='%.3f' % order.qty, amt=f"{float('%.3f' % order.amt):,}").send_email()

        return jsonify({'err': 0, 'msg': 'Deposit approved successfully'})
    elif action == "reject":
        if not current_user.privilege.deposit_reject and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        order.status = 1
        current_user.record_action('reject_deposit', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        # EmailSender('reject_deposit', id=order.time, email=order.user.email, method=order.method.upper(),
        #             qty='%.3f' % order.qty, amt=f"{float('%.3f' % order.amt):,}").send_email()
        return jsonify({'err': 0, 'msg': 'Deposit rejected'})
    elif action == "erase":
        if not current_user.privilege.deposit_erase and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        db.session.delete(order)
        current_user.record_action('erase_deposit', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        return jsonify({'err': 0, 'msg': 'Request erased successfully'})


@app.route('/admin/get_members', methods=['POST'])
@login_required
def admin_get_members():
    if request.form['action'] == "fetch":
        members = db.session.query(Member).order_by(Member.id.desc()).all()
        content = ''
        sn = 0
        if members is not None:
            for user in members:
                if current_user.has_access_to(user):
                    if user.logins is None:
                        last_access = "-"
                    else:
                        last_access = format_date(user.logins.split('=')[1], '%d %b %H:%M')

                    # Calculate total withdrawal
                    total_ref_earning = 0
                    total_with = 0
                    total_deposit = 0
                    for history in user.history:
                        if history.name == "ref_earning":
                            total_ref_earning += history.amt
                        if history.name == "withdraw" and history.status == 1:
                            total_with += history.amt
                        if history.name == "deposit" and history.status == 1:
                            total_deposit += history.amt

                    if user.upline:
                        upline = Member.query.filter_by(id=user.upline).first().email.split('@')[0]
                    else:
                        upline = ''

                    status_class, status_text = get_status_details(0 if user.suspend else 1, zero='Suspended', one='Active', two='Rejected')

                    sn += 1
                    content += f"""
                        <tr class='trs' data-bs-toggle='modal' data-bs-target='#exampleLargeModal1' >
                            <td>{sn}</td>
                            <td>{user.email}</td>
                            <td>{user.level}</td>
                            <td>~{upline}</td>
                            <td>{user.country.split(':')[0]}</td>
                            <td>{format_date(user.reg_date, '%d %b')} ({st(user.reg_date)} ago)</td>
                            <td>{user.fullname}</td>
                            <td>{last_access}</td>
                            <td>${user.earning:,.2f}</td>
                            <td>${user.promotion:,.2f}</td>
                            <td>${user.ref_earning:,.2f}</td>
                            <td>${total_ref_earning:,.2f}</td>
                            <td>${total_deposit:,.2f}</td>
                            <td>${total_with:,.2f}</td>
                            <td>
                                <div class='badge rounded-pill w-100 text-bg-{status_class}'>{status_text}</div>
                            </td>
                            <td>
                                <div class='dropdown ms-auto'>
                                <div data-bs-toggle='dropdown' class='dropdown-toggle dropdown-toggle-nocaret cursor-pointer' aria-expanded='false'>
                                    <i class='bx bx-dots-vertical-rounded font-22'></i>
                                </div>
                                <ul class='dropdown-menu' style='cursor: pointer'>
                                <li data-id='{user.id}' data-action='suspend' class='member_actions'><a class='dropdown-item'><i class='bx bx-minus-circle font-22'></i> Suspend/Unsuspend</a></li>
                                    <li><hr class='dropdown-divider'></li>
                                    <li data-id='{user.id}' data-action='erase' class='member_actions'><a class='dropdown-item'><i class='bx bx-x-circle font-22'></i> Delete</a></li>
                                    <li><hr class='dropdown-divider'></li>
                                </ul>
                            </td>
                        </tr>
                    """
        return jsonify({'content': content})
    else:
        user = Member.query.filter_by(id=request.form['id']).first()
        if request.form['action'] == "suspend":
            if user.suspend:
                user.suspend = False
            else:
                user.suspend = True
        elif request.form['action'] == "erase":
            db.session.delete(user)
        db.session.commit()
    return jsonify({'status': 'success', 'message': 'Action successful'})


@app.route('/admin/videos', methods=['POST', 'GET'])
@login_required
def admin_videos():
    if request.method == "GET":
        return render_template('admin/videos.html')
    elif request.method == "POST":
        if request.form['action'] == "fetch":
            videos = db.session.query(Video).order_by(Video.id.desc()).all()
            content = ''
            sn = 0
            if videos:
                for video in videos:
                    sn += 1
                    status_class, status_text = get_status_details(video.status, zero='Pending Review', one='Active', two='Rejected')
                    content += f"""
                            <tr data-image_name="{video.image_name}" data-video_name="{video.video_name}" data-video_id="{video.video_id}" data-title="{video.title}" data-category="{video.category}" data-description="{video.description}" data-id="{video.id}" class='trs'>
                                <td>{sn}</td>
                                <td>{video.user.email if video.user else None}</td>
                                <td data-bs-toggle='modal' data-bs-target='#videoModal' style="text-decoration: underline">{video.title}</td>
                                <td>~{video.category}</td>
                                <td>{video.count:,}</td>
                                <td>{format_date(video.time, '%d %b %H:%M')}</td>
                                <td>{format_date(video.modified, '%d %b %H:%M') if video.modified else None}</td>
                                <td>
                                    <div class='badge rounded-pill w-100 text-bg-{status_class}'>{status_text}</div>
                                </td>
                                <td>
                                    <div class='dropdown ms-auto'>
                                    <div data-bs-toggle='dropdown' class='dropdown-toggle dropdown-toggle-nocaret cursor-pointer' aria-expanded='false'>
                                        <i class='bx bx-dots-vertical-rounded font-22'></i>
                                    </div>
                                    <ul class='dropdown-menu' style='cursor: pointer'>
                        """
                    if video.status == 0:
                        content += f"""
                                <li data-id='{video.id}' data-action='activate' class='video_actions'><a class='dropdown-item'><i class='bx bx-check font-22 '></i> Activate</a></li>
                                """
                    if video.status == 1:
                        content += f"""
                                <li data-id='{video.id}' data-action='deactivate' class='video_actions'><a class='dropdown-item'><i class='bx bx-x-circle font-22'></i> Reject</a></li>
                                <li><hr class='dropdown-divider'></li>
                                <li data-id='{video.id}' data-action='pend' class='video_actions'><a class='dropdown-item'><i class='bx bx-minus-circle font-22'></i> Keep Pending</a></li>
                                <li><hr class='dropdown-divider'></li>
                                """

                    content += f"""
                            <li data-id='{video.id}' data-action='erase' class='video_actions'><a class='dropdown-item' style='color: red; font-weight: bold'><i class='bx bx-trash font-22'></i> Erase Video</a></li>
                                </ul>
                                </div>
                            </td>
                            </tr>
                            """

            return content

        elif request.form['action'] == "modify":
            video = Video.query.filter_by(id=request.form['id']).first()
            if video:
                video.title = request.form['title']
                video.description = request.form['description']
                video.category = request.form['category']
                db.session.commit()
                return jsonify({'status': 'success', 'message': 'Action success'})
            return jsonify({'status': 'error', 'message': 'Video doesnt exist'})
        else:
            video = Video.query.filter_by(id=request.form['id']).first()
            if video:
                if request.form['action'] == "activate":
                    video.status = 1
                elif request.form['action'] == "deactivate":
                    video.status = 2
                elif request.form['action'] == "pend":
                    video.status = 0
                elif request.form['action'] == "erase":
                    video_path = os.path.join(app.config['VIDEO_UPLOAD_FOLDER'], video.video_name)
                    image_path = os.path.join(app.config['IMAGE_UPLOAD_FOLDER'], video.image_name)
                    try:
                        os.remove(image_path)
                        os.remove(video_path)
                        db.session.delete(video)
                        db.session.commit()
                    except OSError:
                        return jsonify({'status': 'error', 'message': f'Error deleting content'})
                db.session.commit()
            return jsonify({'status': 'success', 'message': 'Action success'})


@app.route('/admin/tf_actions', methods=['POST'])
@login_required
def admin_tf_actions():
    action = request.form.get('action')
    id_ = request.form.get('id')
    order = db.session.query(ProgramHistory).filter_by(id=id_).first()
    if order is None:
        return jsonify({'err': 1, 'msg': 'This order does not exist!'})
    if action == "erase":
        if not current_user.privilege.transfer_erase and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        db.session.delete(order)
        current_user.record_action('erase_tf', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        return jsonify({'err': 0, 'msg': 'Record erased successfully'})


@app.route('/admin/with_actions', methods=['POST'])
@login_required
def admin_with_actions():
    action = request.form.get('action')
    id_ = request.form.get('id')
    order = db.session.query(ProgramHistory).filter_by(id=id_).first()
    if order is None:
        return jsonify({'err': 1, 'msg': 'This order does not exist!'})
    if action == "paid":
        if not current_user.privilege.withdraw_approve and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        order.status = 1
        current_user.record_action('approve_with', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        return jsonify({'err': 0, 'msg': 'Withdrawal marked successfully'})
    elif action == "reject":
        if not current_user.privilege.withdraw_reject and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        order.status = 2
        current_user.record_action('reject_with', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        return jsonify({'err': 0, 'msg': 'Withdrawal rejected'})
    elif action == "erase":
        if not current_user.privilege.withdraw_erase and current_user.role != "admin":
            return jsonify({'err': 1, 'msg': 'No privileges'})
        db.session.delete(order)
        current_user.record_action('erase_with', order.user.email,
                                   method=order.method, qty='%.3f' % order.qty, id=order.id)
        return jsonify({'err': 0, 'msg': 'Request has been erased'})


@app.route('/admin/tasks', methods=['POST'])
@login_required
def admin_tasks():
    action = request.form.get('action')
    user: Member = load_member(request.form.get('username'))

    msg = 'Notes retrieved'
    if action == "add":
        new_task = Task(
            admin_t_id=current_user.id,
            member_t_id=user.id,
            body=request.form.get('input'),
            time=int(dt.datetime.now().timestamp())
        )
        db.session.add(new_task)
        msg = "Note added"
    elif action == "status":
        task = db.session.query(Task).filter_by(id=request.form.get('id')).first()
        if task is None:
            return jsonify({'err': 1, 'msg': 'Note does not exist'})
        if task.status == 1:
            task.status = 0
            msg = "Note unmarked"
        else:
            task.status = 1
            msg = "Note marked"
    elif action == "delete":
        task = db.session.query(Task).filter_by(id=request.form.get('id')).first()
        if task is None:
            return jsonify({'err': 1, 'msg': 'Note does not exist'})
        db.session.delete(task)
        msg = "Note deleted"
    elif action == "edit":
        task = db.session.query(Task).filter_by(id=request.form.get('id')).first()
        if task is None:
            return jsonify({'err': 1, 'msg': 'Note does not exist'})
        if task.body != request.form.get('input'):
            task.body = request.form.get('input')
            task.edited = True
            msg = "Note updated"
    db.session.commit()

    # GET TASKS
    content = ''
    if user.tasks:
        for x in range(len(user.tasks) - 1, -1, -1):
            row = user.tasks[x]
            checked = ""
            edited = ""
            if row.status == 1:
                checked = "checked"
            if row.edited:
                edited = "📢 edited"
            content += f"""
            <div class="col-12">
                            							<div id="todo-container">
                            								    <div class="pb-3 todo-item">
                                                    				<div class="input-group">

                                                    						<div class="input-group-text">
                                                    							<input type="checkbox" aria-label="Checkbox for following text input" data-id="{row.id}" {checked}  class="task-status">
                                                    						</div>

                                                    					    <textarea data-id="{row.id}" class="form-control old-task" rows=2>{row.body}</textarea>

                                                    						<button class="btn btn-outline-secondary bg-danger text-white delete-task" data-id="{row.id}" type="button">X</button>
                                                    					<div style="width: 100%; display: inine-block; background: #E9ECEF" >
                                                    					<span style="float: left; width: 40%" class="px-2">
                                                    					By: {row.owner.fname}
                                                    					</span>
                                                    					    <span style="float: right; width: 40%; text-align: right" class="px-2">-{format_date(row.time, '%a %d %b, %Y @ %I:%M %p')} <span style="font-weight: bold" class="text-primary">{edited}</span>
                                                    					    </span>
                                                    					    </div>

                                                    				  </div>
                                            			        </div>
                                            			    </div>
                            							</div>
            """

    return jsonify({'err': 0, 'msg': msg, 'tasks': content})


@csrf.exempt
@app.route('/webhook', methods=['POST'])
def webhook():
    # if request.remote_addr != "5.189.219.250":
    #     return 'error'
    user_id, action = request.form['label'].split('=')
    user_id = int(user_id)
    qty = float(request.form['amount'])
    currency = request.form['currency'].lower()
    status = request.form['status']
    address = request.form['address']
    tx_id = request.form['id']
    amt_usd = api.get_ticker_from_binance(currency, conversion=True, direction="fd", amount=qty)
    tx_status = 1
    if status == "completed":
        user = db.session.query(Member).filter_by(id=user_id).first()
        address_result = Address.query.filter_by(wallet=address).first()
        if user and address_result and not ProgramHistory.query.filter_by(tx_id=tx_id).first():
            if action == "complete":
                rate_time = int(address_result.rate_time)
                qty_to_pay = address_result.qty_to_pay \
                    if rate_time + (30*60) > get_timestamp() \
                    else api.get_ticker_from_binance(currency, conversion=True, direction="bk", amount=address_result.amt_to_pay)
                if qty >= qty_to_pay and address_result.upgrade_level > 0:
                    # PAYMENT FOR COMPLETE UPGRADE VALID
                    leftover = qty - qty_to_pay
                    if user.today_watch == PLANS[user.level]['videos']:
                        user.today_watch = PLANS[address_result.upgrade_level]['videos']
                    user.level = address_result.upgrade_level
                    user.earning += leftover
                    new_notif = Notification(member_id=user_id, category='upgrade', time=get_timestamp(),
                                             body=f'Upgrade to level {address_result.upgrade_level} completed. Enjoy the experience!')
                    db.session.add(new_notif)
                    Address.query.filter(Address.member_id == user_id, Address.label == 'complete').update(
                        {'upgrade_level': 0})
                    tx_status = 1
                else:
                    # PAYMENT INCOMPLETE, JUST SAVE TO HISTORY WITH STATUS 0
                    tx_status = 0
            # SAVE HISTORY
            new_tx = ProgramHistory(name="deposit", amt=amt_usd, method=currency, status=tx_status,
                                    qty=qty, time=get_timestamp(), member_id=user.id, tx_id=tx_id, wallet=address)
            db.session.add(new_tx)
            db.session.commit()
            return jsonify({'status': 'success'})
        return jsonify({'err': 'An error occurred'}), 404
    return jsonify({'status': 'OK'}), 200


@csrf.exempt
@app.route('/daily_update', methods=['POST'])
def daily_update():
    members = Member.query.all()
    for member in members:
        watch = PLANS[member.level]['videos']
        member.today_watch = watch
        member.video_views = []
    db.session.commit()
    return jsonify({'status': 'success'})


@csrf.exempt
@app.route('/execute', methods=['POST'])
def execute():
    videos = Video.query.all()
    for video in videos:
        video.creator = 1
    db.session.commit()
    return jsonify({'status': 'success'})


if __name__ == '__main__':
    app.run()

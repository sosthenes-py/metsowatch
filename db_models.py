from flask_sqlalchemy import SQLAlchemy
from flask_login import UserMixin

db = SQLAlchemy()


class Member(UserMixin, db.Model):
    __tablename__ = "members"
    id = db.Column(db.Integer, primary_key=True)
    fullname = db.Column(db.String(250), default='')
    email = db.Column(db.String(250), nullable=False)
    email2 = db.Column(db.String(250), default='')
    email_status = db.Column(db.Boolean, default=False)
    twofa_secret = db.Column(db.String(250))
    twofa_status = db.Column(db.Boolean, default=False)
    session_id = db.Column(db.String(250))
    password = db.Column(db.String(250), nullable=False)
    code = db.Column(db.String(250))
    logins = db.Column(db.String)
    pin = db.Column(db.String)
    earning = db.Column(db.Float, default=0.0)
    promotion = db.Column(db.Float, default=0.0)
    ref_earning = db.Column(db.Float, default=0.0)
    country = db.Column(db.String)
    phone = db.Column(db.String)
    upline = db.Column(db.String)
    level = db.Column(db.Integer)
    today_watch = db.Column(db.Integer, default=0)
    reg_date = db.Column(db.String(250))
    otp = db.Column(db.String(250))
    otp_time = db.Column(db.String(250))
    suspend = db.Column(db.Boolean, default=False)

    history = db.relationship("ProgramHistory", backref="user", cascade="all, delete-orphan")
    addresses = db.relationship("Address", backref="user", cascade="all, delete-orphan")
    sessions = db.relationship("Session", backref="user", cascade="all, delete-orphan")
    downlines = db.relationship('Downline', backref="upline", cascade="all, delete-orphan")
    notifications = db.relationship("Notification", backref="user", cascade="all, delete-orphan")
    payout_accounts = db.relationship("PayoutAccount", backref="user", cascade="all, delete-orphan")
    contents = db.relationship("Video", backref="user", cascade="all, delete-orphan")
    video_views = db.relationship("VideoWatched", backref="user", cascade="all, delete-orphan")


class ProgramHistory(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    member_id = db.Column(db.Integer, db.ForeignKey('members.id', name="program_history_member_fk"), nullable=False)
    name = db.Column(db.String(250), nullable=False)
    amt = db.Column(db.Float, nullable=False, default=0)
    method = db.Column(db.String(250))
    time = db.Column(db.String(250), nullable=False)
    wallet = db.Column(db.String(250))
    status = db.Column(db.Integer, default=0)
    hash = db.Column(db.String(250))
    label = db.Column(db.String(250))
    price = db.Column(db.String(250))
    qty = db.Column(db.Float, default=0)
    detail = db.Column(db.String(250))
    fee = db.Column(db.Float, default=0)
    tx_id = db.Column(db.String)
    stack = db.Column(db.Integer, default=1)


class Address(db.Model):
    __tablename__ = "address"
    id = db.Column(db.Integer, primary_key=True)
    member_id = db.Column(db.Integer, db.ForeignKey('members.id', name="address_member_fk"), nullable=False)
    token = db.Column(db.String(250), nullable=False)
    wallet = db.Column(db.String(250), nullable=False)
    label = db.Column(db.String(250))
    amt_to_pay = db.Column(db.Float, default=0)
    qty_to_pay = db.Column(db.Float, default=0)
    hold_amt = db.Column(db.Float, default=0)
    leftover = db.Column(db.Float, default=0)
    upgrade_level = db.Column(db.Integer, default=0)
    time = db.Column(db.String(250), nullable=False)
    rate_time = db.Column(db.String(250))


class PayoutAccount(db.Model):
    __tablename__ = "payout_account"
    id = db.Column(db.Integer, primary_key=True)
    member_id = db.Column(db.Integer, db.ForeignKey('members.id', name="payout_account_member_fk"), nullable=False)
    token = db.Column(db.String(250), nullable=False)
    wallet = db.Column(db.String(250), nullable=False)
    label = db.Column(db.String(250))
    time = db.Column(db.String(250), nullable=False)
    account_id = db.Column(db.String(250))


class Session(db.Model):
    __tablename__ = "sessions"
    id = db.Column(db.Integer, primary_key=True)
    member_id = db.Column(db.Integer, db.ForeignKey('members.id', name="sessions_member_fk"), nullable=False)
    token = db.Column(db.String(250), nullable=False)
    ip = db.Column(db.String(250), nullable=False)
    device = db.Column(db.String(250), default='')
    time = db.Column(db.String(250), nullable=False)


class Downline(db.Model):
    __tablename__ = "downlines"
    id = db.Column(db.Integer, primary_key=True)
    upline_id = db.Column(db.Integer, db.ForeignKey('members.id', name="member_upline_fk"))
    downline_id = db.Column(db.Integer, nullable=False)
    downline_name = db.Column(db.String, default='')
    level = db.Column(db.Integer, nullable=False)
    time = db.Column(db.String(250), nullable=False)


class Video(db.Model):
    __tablename__ = "videos"
    id = db.Column(db.Integer, primary_key=True)
    video_id = db.Column(db.String(250), nullable=False)
    category = db.Column(db.String(250))
    title = db.Column(db.String(250), default='')
    image_name = db.Column(db.String(250), default='website-default.jpg')
    video_name = db.Column(db.String(250))
    description = db.Column(db.String(2000))
    creator = db.Column(db.Integer, db.ForeignKey('members.id', name="member_video_fk"))
    length = db.Column(db.Integer, nullable=False)
    time = db.Column(db.String(250), nullable=False)
    modified = db.Column(db.String(250))
    status = db.Column(db.Integer(), default=0)
    count = db.Column(db.Integer(), default=0)
    earning = db.Column(db.Float(), default=0)
    yt_id = db.Column(db.String())

    views = db.relationship("VideoWatched", backref="video",  cascade="all, delete-orphan")


class VideoWatched(db.Model):
    __tablename__ = "videos_watched"
    id = db.Column(db.Integer, primary_key=True)
    video_id = db.Column(db.String(250), db.ForeignKey('videos.video_id', name="video_video_watched_fk"), nullable=False)
    member_id = db.Column(db.String(250), db.ForeignKey('members.id', name="member_video_watched_fk"), nullable=False)
    time = db.Column(db.String(250), nullable=False)


class Notification(db.Model):
    __tablename__ = "notifications"
    id = db.Column(db.Integer, primary_key=True)
    member_id = db.Column(db.Integer, db.ForeignKey('members.id', name="member_notifications_fk"), nullable=False)
    category = db.Column(db.String, nullable=False)
    body = db.Column(db.String, nullable=False)
    status = db.Column(db.Integer, default=0)
    time = db.Column(db.String(250), nullable=False)


class AdminMember(UserMixin, db.Model):
    __tablename__ = "admins"
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(250), nullable=False)
    suspend = db.Column(db.Boolean, default=False)
    password = db.Column(db.String(250), nullable=False)
    fname = db.Column(db.String(250))
    lname = db.Column(db.String(250), default='')
    logins = db.Column(db.String(250))
    role = db.Column(db.String(250))

    def has_access_to(self, user):
        return self.role == "admin"


class Task(db.Model):
    __tablename__ = "tasks"
    id = db.Column(db.Integer, primary_key=True)
    admin_t_id = db.Column(db.Integer, db.ForeignKey('admins.id', name="tasks_admin_fk"), nullable=False)
    member_t_id = db.Column(db.Integer, db.ForeignKey('members.id', name="tasks_member_fk"), nullable=False)
    body = db.Column(db.String(10000), nullable=False)
    status = db.Column(db.Integer, default=0)
    time = db.Column(db.String(250), nullable=False)
    edited = db.Column(db.Boolean, default=False)


class Visit(db.Model):
    __tablename__ = "visits"
    id = db.Column(db.Integer, primary_key=True)
    device = db.Column(db.String(1000), nullable=False)
    time = db.Column(db.String(250))

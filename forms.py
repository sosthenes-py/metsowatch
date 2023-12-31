from flask_wtf import FlaskForm
from wtforms import EmailField, PasswordField, StringField, SubmitField, IntegerField, TelField, FloatField, SelectField
from wtforms.validators import DataRequired, Email, Length, ValidationError
from flask_wtf.file import FileAllowed, FileRequired, FileField


class LoginForm(FlaskForm):
    email = EmailField(validators=[DataRequired(message="The email field is required"), Email(), Length(min=5, max=60, message="Email too short")])
    password = PasswordField(validators=[DataRequired(message="The password field is required"), Length(min=8, max=60, message='Password too short')])
    submit = SubmitField()


class RegisterForm(FlaskForm):
    email = EmailField(validators=[DataRequired(message="The email field is required"), Email(), Length(min=5, max=60, message="Email too short")])
    password = PasswordField(validators=[DataRequired(message="The password field is required"), Length(min=8, max=60, message="Password too short")])
    submit = SubmitField()


class TwoFaForm(FlaskForm):
    code = StringField(validators=[DataRequired(message="Please enter code from your Authenticator App")])
    submit = SubmitField(label="Setup 2FA")


class AddWithdrawAccountForm(FlaskForm):
    name = StringField(validators=[DataRequired(message="Please enter method name"), Length(max=20, message="Name accepts max of 20 characters")])
    wallet = StringField(validators=[DataRequired(message="Enter wallet address for this method"), Length(max=100, message="Wallet too long")])
    submit = SubmitField(label="")


class WithdrawForm(FlaskForm):
    acct_id = StringField(validators=[DataRequired(message="Please select withdrawal account"), Length(max=10, message="Account ID error")])
    deduct_from = StringField(
        validators=[DataRequired(message="Please provide deduction wallet"), Length(max=20, message="Account ID error")])
    amt = FloatField(validators=[DataRequired(message="Withdrawal amount is required")])
    twofa_code = StringField(validators=[Length(max=10, message="2FA code error")])
    submit = SubmitField(label="")


class ChangePasswordForm(FlaskForm):
    old_pass = PasswordField(validators=[DataRequired(message="Please enter current password"), Length(min=8, max=60, message="Password too short")])
    new_pass = PasswordField(validators=[DataRequired(message="Enter your new password"), Length(min=8, max=60, message="Password too short")])
    new_pass2 = PasswordField(validators=[DataRequired(message="Confirm password"),
                                         Length(min=8, max=60, message="Password too short")])
    twofa_code = StringField(validators=[Length(max=10, message="2FA code error")])
    submit = SubmitField()


MAX_FILE_SIZE_BYTES = 1024 * 1024  # 120 MB


class CreateVideoForm(FlaskForm):
    title = StringField(validators=[DataRequired(message="Please provide video title"), Length(max=50, min=5)])
    category = StringField(validators=[Length(max=50)])
    description = StringField(validators=[DataRequired(message="Please provide video description"), Length(max=1900, min=5)])
    submit = SubmitField(label="")


class AdminLoginForm(FlaskForm):
    email = EmailField('Email Address', validators=[DataRequired(), Email()])
    password = PasswordField('Password', validators=[DataRequired()])


class AdminRegisterForm(FlaskForm):
    fname = StringField('First Name', validators=[DataRequired()])
    email = EmailField('Email Address', validators=[DataRequired(), Email()])
    password = PasswordField('Password', validators=[DataRequired()])
    role = SelectField('Select a role', choices=[
        ('admin', 'Admin Role'),
        ('agent', 'Agent Role')
    ])


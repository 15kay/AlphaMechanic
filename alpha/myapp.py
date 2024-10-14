
from flask import Flask, render_template, request, redirect, url_for, session, flash
from flask_mail import Mail, Message
import sqlite3

app = Flask(__name__)
app.secret_key = 'your_secret_key'

# Set up mail configuration (make sure these are set in your environment or app config)
app.config['MAIL_SERVER'] = 'smtp.example.com'
app.config['MAIL_PORT'] = 587
app.config['MAIL_USERNAME'] = 'your_username'
app.config['MAIL_PASSWORD'] = 'your_password'
app.config['MAIL_USE_TLS'] = True
app.config['MAIL_USE_SSL'] = False
mail = Mail(app)

# Function to get DB connection
def get_db_connection():
    conn = sqlite3.connect('database.db')
    conn.row_factory = sqlite3.Row
    return conn

@app.route('/track_ord', methods=['GET', 'POST'])
def track_order():
    if request.method == 'POST':
        OrderNumber = request.form.get('orderNumber')
        if not OrderNumber or len(OrderNumber) < 5:
            flash('Invalid order number. Please enter a valid order number.', 'warning')
            return redirect(url_for('track_order'))

        # Query the order by OrderNumber
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT OrderNumber, Status FROM Customer_Orders WHERE OrderNumber = ?', (OrderNumber,))
        orderStatus = cursor.fetchall()
        cursor.close()
        conn.close()

        if not orderStatus:
            flash('Order not found. Please check your order number.', 'warning')
            return redirect(url_for('track_order'))

        return render_template('track_orders.html', orderStatus=orderStatus)

    # If it's a GET request, render the tracking form
    if 'user_fullname' in session:
        return render_template('New_Customer/track_orders.html', fullname=session['user_fullname'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
@app.route('/login')
def login_page():
    return render_template('login.html')
@app.route('/forgot-password')
def forgot_password():
    return render_template('forgot_password.html')
@app.route('/registers')
def register():
    return render_template('registration.html')
@app.route('/')
def index():
    return render_template('index.html')
@app.route('/edit-profile/<int:user_id>')
def edit_profile(user_id):
    if 'user_fullname' in session:
        return render_template('edit_profile.html', user_id=user_id)
    else:
        flash('Please log in to edit your profile.', 'warning')
        return redirect(url_for('login_page'))


@app.route('/send-email/<int:user_id>/<int:order_id>')
def send_email(user_id, order_id):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()

        # Fetching order details
        cursor.execute('SELECT OrderDate FROM Customer_Orders WHERE PersonID = ? AND OrderNumber = ?', (user_id, order_id))
        order_date = cursor.fetchone()[0]

        # Fetching order parts
        cursor.execute('SELECT Parts FROM Customer_Orders WHERE PersonID = ?', (user_id,))
        order_parts = cursor.fetchall()
        order_parts_str = ', '.join(part[0] for part in order_parts) if order_parts else 'No parts ordered'

        # Fetch user details
        cursor.execute('SELECT fullname, Email FROM Persons WHERE PersonID = ?', (user_id,))
        details = cursor.fetchone()
        if not details:
            return 'No user details found.'

        customer_name = details[0]
        recipient_email = details[1]
    except Exception as e:
        return f'Database error: {str(e)}'
    finally:
        cursor.close()
        conn.close()

    # Attempt to render the email template
    try:
        rendered_template = render_template(
            "New_Customer/email_template.html",
            customer_name=customer_name,
            order_id=order_id,
            order_date=order_date,
            order_parts=order_parts_str
        )
    except Exception as e:
        return f'Failed to render template: {str(e)}'

    # Create email message
    msg = Message('Order Confirmation | ALPHA MECHANIC', recipients=[recipient_email])
    msg.html = rendered_template

    # Attach the logo image
    try:
        with app.open_resource('static/logo1.png') as logo:
            msg.attach('logo1.png', 'image/png', logo.read(), 'inline', headers=[['Content-ID', '<logo1>']])
    except Exception as e:
        return f'Failed to attach logo: {str(e)}'

    try:
        # Send email
        mail.send(msg)
        return 'Email sent successfully!'
    except Exception as e:
        return f'Failed to send email: {str(e)}'


if __name__ == "__main__":
    app.run(debug=True, port=5001)

"""empty message

Revision ID: 77bb35aecfa3
Revises: 3e4aed098eb0
Create Date: 2023-12-24 01:01:31.769941

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '77bb35aecfa3'
down_revision = '3e4aed098eb0'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    with op.batch_alter_table('videos', schema=None) as batch_op:
        batch_op.add_column(sa.Column('status', sa.Integer(), nullable=True))

    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    with op.batch_alter_table('videos', schema=None) as batch_op:
        batch_op.drop_column('status')

    # ### end Alembic commands ###
